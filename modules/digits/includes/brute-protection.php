<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}


class DigitsBruteForceProtection
{

    const TABLE_NAME = 'digits_failed_login_logs';
    static $brute_force_allowed_ip = false;
    /**
     * The single instance of the class.
     * @var DigitsBruteForceProtection|null
     */
    private static $instance = null;
    private static $digits_shield = -1;
    /**
     * Database table name for login attempts.
     * @var string
     */
    private $table_name;
    /**
     * WordPress database object.
     * @var wpdb
     */
    private $wpdb;
    /**
     * Maximum number of failed login attempts allowed.
     * @var int
     */
    private $max_attempts = 20;
    /**
     * Time period (in seconds) to look back for failed attempts.
     * @var int
     */
    private $lookback_period = 2500;
    /**
     * Lockout duration (in seconds) after exceeding max attempts.
     * @var int
     */
    private $lockout_duration = 3600;

    /**
     * Constructor.
     */
    private function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $this->wpdb->prefix . self::TABLE_NAME;

        add_action('digits_create_database', [$this, 'create_database']);
        $this->add_hooks();
    }

    private function add_hooks()
    {

        add_action('digits_check_user_login', array($this, 'check_user_login'), 10, 2);
        add_action('digits_check_user_forgotpass', array($this, 'check_user_login'), 10, 2);
        add_filter('authenticate', [$this, 'check_lockout'], 30, 3);
        add_action('wp_login_failed', [$this, 'handle_failed_login'], 10, 1);
        add_filter('authenticate', [$this, 'add_remaining_attempts_message'], 120, 3);

    }

    /**
     * @throws Exception
     */
    public static function add_invalid_otp_attempt($details)
    {
        $details['failure_reason'] = 'invalid_otp';
        $details['is_blocked'] = 0;
        $self = self::$instance;

        $self->record_failed_attempt(false, $details);

        $recent_failures = $self->count_recent_failures(false, $details);

        $remaining_attempts = $self->max_attempts - $recent_failures;

        if ($remaining_attempts > 0 && $remaining_attempts <= 3 && $recent_failures > 0) {
            $warning_message = sprintf(
                _n(
                    'You have %d login attempt remaining before being temporarily locked out.',
                    'You have %d login attempts remaining before being temporarily locked out.',
                    $remaining_attempts,
                    'digits'
                ),
                $remaining_attempts
            );

            throw new Exception($warning_message);
        }

        if ($recent_failures >= $self->max_attempts) {
            $self->apply_lockout(false, $details);
            throw new Exception($self->failed_attempts_wait_message()->get_error_message());
        }
    }

    /**
     * Record a failed login attempt in the database.
     *
     * @param string $ip_address The IP address.
     * @param array $data The user data
     */
    private function record_failed_attempt($ip_address, $data)
    {
        if (empty($ip_address)) {
            $ip_address = digits_get_ip();
        }
        $data = $this->parse_user_from_data($data);

        $data['ip_address'] = $ip_address;
        $data['attempt_time'] = current_time('mysql', true);
        $data['locked_until'] = null;

        $this->wpdb->insert(
            $this->table_name,
            $data
        );
    }

    private function parse_user_from_data($data)
    {
        if (!empty($data['user_id'])) {
            return $data;
        }
        $user = false;
        if (!empty($data['username'])) {
            $user = get_user_by('login', $data['username']);
        } else if (!empty($data['email'])) {
            $user = get_user_by('email', $data['email']);
        } else if (!empty($data['phone'])) {
            $user = getUserFromPhone($data['phone']);
        }

        if (!empty($user) && $user instanceof WP_User) {
            $data['user_id'] = $user->ID;

            if (empty($data['username'])) {
                $data['username'] = $user->user_login;
            }

            if (empty($data['email'])) {
                $data['email'] = $user->user_email;
            }

            if (empty($data['phone'])) {
                $data['phone'] = digits_get_mobile($user->ID);
            }

        }
        return $data;
    }

    /**
     * Count recent failed login attempts for a given IP.
     *
     * @param string $ip_address The IP address.
     * @param array $data The user data
     * @return int Number of recent failures.
     */
    private function count_recent_failures($ip_address, $data)
    {
        $data = $this->parse_user_from_data($data);
        $lookback_time_gmt = gmdate('Y-m-d H:i:s', time() - $this->lookback_period);

        $lookout_obj = $this->get_lookout_values($ip_address, $data);
        $lookout_key = $lookout_obj['key'];
        $lookout_value = $lookout_obj['value'];

        $count = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$this->table_name}
             WHERE {$lookout_key} = %s
             AND attempt_time > %s AND is_blocked = 0",
            $lookout_value,
            $lookback_time_gmt
        ));

        return $count;
    }

    public function get_lookout_values($ip_address, $data)
    {
        $lookout_key = 'ip_address';
        $lookout_value = $ip_address;

        if (!empty($data['user_id'])) {
            $lookout_key = 'user_id';
            $lookout_value = $data['user_id'];
        } else if (!empty($data['phone'])) {
            $lookout_key = 'phone';
            $lookout_value = $data['phone'];
        } else if (!empty($data['email'])) {
            $lookout_key = 'email';
            $lookout_value = $data['email'];
        }
        return ['key' => $lookout_key, 'value' => $lookout_value];
    }

    /**
     * Apply a lockout to an IP address by updating the latest attempt record.
     *
     * @param string $ip_address The IP address to lock out.
     */
    private function apply_lockout($ip_address, $data)
    {
        if (empty($ip_address)) {
            $ip_address = digits_get_ip();
        }

        $lookout_obj = $this->get_lookout_values($ip_address, $data);
        $lookout_key = $lookout_obj['key'];
        $lookout_value = $lookout_obj['value'];


        $lockout_until_time = time() + $this->lockout_duration;
        $lockout_until_gmt = gmdate('Y-m-d H:i:s', $lockout_until_time);

        // Find the ID of the *most recent* attempt for this IP to mark it as the lockout trigger
        $latest_attempt_id = $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE {$lookout_key} = %s ORDER BY attempt_time DESC LIMIT 1",
            $lookout_value
        ));

        if ($latest_attempt_id) {
            $this->wpdb->update(
                $this->table_name,
                ['locked_until' => $lockout_until_gmt], // Data to update
                ['id' => $latest_attempt_id],            // Where clause
                ['%s'],                                // Format for data
                ['%d']                                 // Format for where
            );
        }
    }

    public function failed_attempts_wait_message()
    {
        $wait_message = sprintf(
            __('Too many failed login attempts. Please try again in %s.', 'digits'),
            human_time_diff(time(), time() + $this->lockout_duration)
        );

        return new WP_Error(
            'user_locked_out',
            '<strong>' . __('ERROR', 'digits') . ':</strong> ' . $wait_message
        );
    }

    public static function is_user_blocked($details)
    {
        $self = self::$instance;

        $lockout_info = $self->is_ip_locked_out(false, $details);
        if ($lockout_info) {
            $wait_message = sprintf(
                __('Too many failed login attempts. Please try again in %s.', 'digits'),
                human_time_diff(time(), $lockout_info['expires_at'])
            );

            $details['failure_reason'] = 'blocked';
            $details['is_blocked'] = 1;


            $self->record_failed_attempt(false, $details);

            return new WP_Error(
                'user_locked_out',
                '<strong>' . __('ERROR', 'digits') . ':</strong> ' . $wait_message
            );
        }
    }

    /**
     * Check if an IP address is currently locked out.
     *
     * @return array|false Lockout info (expires_at timestamp) if locked out, false otherwise.
     */
    private function is_ip_locked_out($ip_address, $data)
    {
        if (empty($ip_address)) {
            $ip_address = digits_get_ip();
        }
        $current_time_gmt = current_time('mysql', true);

        $data = $this->parse_user_from_data($data);

        $lookout_obj = $this->get_lookout_values($ip_address, $data);
        $lookout_key = $lookout_obj['key'];
        $lookout_value = $lookout_obj['value'];

        // Find the latest record for this IP that has a lockout time in the future
        $lockout_record = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT locked_until
             FROM {$this->table_name}
             WHERE {$lookout_key} = %s
             AND locked_until IS NOT NULL
             AND locked_until > %s AND is_blocked = 0
             ORDER BY locked_until DESC
             LIMIT 1",
            $lookout_value,
            $current_time_gmt
        ));

        if ($lockout_record && $lockout_record->locked_until) {
            // Convert stored GMT time to Unix timestamp for comparison
            $expires_at_timestamp = strtotime($lockout_record->locked_until . ' GMT');
            if ($expires_at_timestamp > time()) {
                return ['expires_at' => $expires_at_timestamp];
            }
        }

        return false; // Not locked out
    }

    /**
     * Get the singleton instance of the class.
     *
     * @return DigitsBruteForceProtection
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function check_user_login($validation_error, $user)
    {
        if ($validation_error->has_errors()) {
            return $validation_error;
        }
        $check = $this->check_lockout($user, null, null);
        if ($check instanceof WP_Error) {
            return $check;
        }
        return $validation_error;
    }

    /**
     * Hook: Check if the current IP is locked out before authentication.
     *
     * @param WP_User|WP_Error|null $user User object, error, or null.
     * @param string $username Submitted username.
     * @param string $password Submitted password.
     * @return WP_User|WP_Error|null Original $user if not locked out, WP_Error if locked out.
     */
    public function check_lockout($user, $username, $password)
    {
        if (self::disable_brute_force_protection()) {
            return $user;
        }

        $ip_address = digits_get_ip();

        if (!empty($user) && $user instanceof WP_User) {
            $data['user_id'] = $user->ID;
            $data['username'] = sanitize_user($user->user_login);
        } else {
            $data['username'] = !empty($username) ? $username : null;
        }

        $lockout_info = $this->is_ip_locked_out($ip_address, $data);


        if ($lockout_info) {
            $wait_message = sprintf(
                __('Too many failed login attempts. Please try again in %s.', 'digits'),
                human_time_diff(time(), $lockout_info['expires_at'])
            );

            $data['login_type'] = 'blocked';
            $data['failure_reason'] = 'blocked';
            $data['is_blocked'] = 1;


            $this->record_failed_attempt($ip_address, $data);

            return new WP_Error(
                'user_locked_out',
                '<strong>' . __('ERROR', 'digits') . ':</strong> ' . $wait_message
            );
        }

        return $user;
    }

    public static function disable_brute_force_protection()
    {
        if (self::$digits_shield == -1) {
            self::$digits_shield = get_option('digits_shield', 1);
        }

        if (self::$digits_shield == 0) {
            return true;
        }

        $ip = digits_get_ip();
        if (!self::$brute_force_allowed_ip) {
            self::$brute_force_allowed_ip = get_option("dig_brute_force_allowed_ip");
        }
        if (is_array(self::$brute_force_allowed_ip) && in_array($ip, self::$brute_force_allowed_ip)) {
            return true;
        }
        return false;
    }

    public function create_database()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT DEFAULT NULL,
            ip_address VARCHAR(200) NOT NULL,
            username VARCHAR(255) NULL,
            phone VARCHAR(255) NULL,
            email VARCHAR(255) NULL,
            login_type VARCHAR(255) NULL,
            failure_reason VARCHAR(255) NULL,
            is_blocked TINYINT(1) DEFAULT 0,
            attempt_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            locked_until DATETIME NULL DEFAULT NULL,
            PRIMARY KEY  (id),
            INDEX ip_address_idx (ip_address(200)),
            INDEX attempt_time_idx (attempt_time),
            INDEX locked_until_idx (locked_until)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Hook: Handle a failed login attempt.
     *
     * @param string $username The username used in the failed attempt.
     */
    public function handle_failed_login($username)
    {
        if (self::disable_brute_force_protection()) {
            return;
        }

        $ip_address = digits_get_ip();
        if (!$ip_address) {
            return; // Cannot track without IP
        }

        $data = [
            'username' => sanitize_user($username),
            'login_type' => 'password',
            'failure_reason' => 'invalid_password',
            'is_blocked' => 0,
        ];
        $this->record_failed_attempt($ip_address, $data);

        $recent_failures = $this->count_recent_failures($ip_address, $data);

        if ($recent_failures >= $this->max_attempts) {
            $this->apply_lockout($ip_address, $data);
        }
    }

    /**
     * Filter: Modify the login error message to show remaining attempts.
     *
     * @param string $error The original error message HTML.
     * @return string|WP_Error
     */
    public function add_remaining_attempts_message($error, $username, $password)
    {
        if (self::disable_brute_force_protection()) {
            return $error;
        }

        if (!$error instanceof WP_Error) {
            return $error;
        }
        $error_code = $error->get_error_code();
        if (strpos($error_code, 'user_locked_out') !== false) {
            return $error;
        }


        $is_standard_wp_error = (strpos($error_code, '<strong>' . __('ERROR') . '</strong>') !== false)
            || (strpos($error_code, 'lostpassword') !== false)
            || (strpos($error_code, 'registered') !== false);


        if (!$is_standard_wp_error && strpos($error_code, 'incorrect_password') === false && strpos($error_code, 'invalid_username') === false && strpos($error_code, 'invalid_email') === false) {
            return $error;
        }


        $ip_address = digits_get_ip();
        if (!$ip_address) {
            return $error;
        }


        $data = ['username' => $username];
        $recent_failures = $this->count_recent_failures($ip_address, $data) + 1;

        // Calculate remaining attempts
        $remaining_attempts = $this->max_attempts - $recent_failures;


        if ($remaining_attempts > 0 && $remaining_attempts <= 3 && $recent_failures > 0) {
            $warning_message = sprintf(
                _n(
                    'You have %d login attempt remaining before being temporarily locked out.',
                    'You have %d login attempts remaining before being temporarily locked out.',
                    $remaining_attempts,
                    'digits'
                ),
                $remaining_attempts
            );

            return new WP_Error('attempt-remaining', $warning_message);

        } elseif ($remaining_attempts <= 0 && $recent_failures >= $this->max_attempts) {
            return $this->failed_attempts_wait_message();
        }


        return $error;
    }

}

DigitsBruteForceProtection::get_instance();