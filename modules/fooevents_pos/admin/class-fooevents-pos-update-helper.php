<?php
/**
 * The class that handles the integration functionality for FooEvents POS
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

/**
 * The update helper class for FooEvents POS.
 *
 * @since 1.0.2
 * @package fooevents-pos
 */
class FooEvents_POS_Update_Helper {
	/**
	 * Configuration object
	 *
	 * @var array $config Contains paths and other configurations.
	 */
	private $config;

	/**
	 * Plugin slug
	 *
	 * @var string $slug The current plugin slug.
	 */
	private $slug;

	/**
	 * Plugin data
	 *
	 * @var array $plugin_data The current plugin data.
	 */
	private $plugin_data;

	/**
	 * FooEvents API Key
	 *
	 * @var string $fooevents_api_key The current FooEvents API key.
	 */
	private $fooevents_api_key;

	/**
	 * Envato API Key
	 *
	 * @var string $envato_api_key The current Envato API key.
	 */
	private $envato_api_key;

	/**
	 * Site home URL
	 *
	 * @var string $home_url The current site's home URL.
	 */
	private $home_url;

	/**
	 * FooEvents Response
	 *
	 * @var array $fooevents_reponse The FooEvents plugin update check response.
	 */
	private $fooevents_reponse;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {

		$this->config = array(
			'plugin_file' => WP_PLUGIN_DIR . '/fooevents_pos/fooevents-pos.php',
		);

		$this->fooevents_api_key = get_option( 'globalWooCommerceEventsAPIKey', true );
		$this->envato_api_key    = get_option( 'globalWooCommerceEnvatoAPIKey', true );
		$this->home_url          = get_home_url();

		if ( 1 === (int) $this->fooevents_api_key ) {

			$this->fooevents_api_key = '';

		}

		if ( 1 === (int) $this->envato_api_key ) {

			$this->envato_api_key = '';

		}

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'fooeventspos_set_transitent' ) );
		add_filter( 'plugins_api', array( $this, 'fooeventspos_plugin_update_information' ), 22, 3 );
		add_action( 'in_plugin_update_message-fooevents_pos/fooevents-pos.php', array( $this, 'fooeventspos_show_upgrade_notification' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'fooeventspos_after_plugin_update' ), 10, 2 );
	}

	/**
	 * Filter the response for the current WordPress.org Plugin Installation API request.
	 *
	 * @since 1.0.2
	 * @param object $result The result object or array.
	 * @param string $action The type of information being requested from the Plugin Installation API.
	 * @param object $args Plugin API arguments.
	 *
	 * @return object The result object.
	 */
	public function fooeventspos_plugin_update_information( $result, $action, $args ) {

		if ( 'plugin_information' !== $action ) {

			return $result;

		}

		$plugin_slug = 'fooevents_pos/fooevents-pos.php';

		if ( $plugin_slug !== $args->slug ) {

			return $result;

		}

		$remote = get_transient( 'fooevents_update_' . $plugin_slug );

		if ( false === $remote ) {

			$remote = wp_remote_get(
				'https://www.fooevents.com/update_info/fooevents_pos.json',
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && 200 === $remote['response']['code'] && ! empty( $remote['body'] ) ) {

				set_transient( 'fooevents_update_' . $plugin_slug, $remote, 43200 ); // 12 hours cache

			}
		}

		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && 200 === $remote['response']['code'] && ! empty( $remote['body'] ) ) {

			$remote = json_decode( $remote['body'] );
			$result = new stdClass();

			$result->name           = $remote->name;
			$result->slug           = $plugin_slug;
			$result->version        = $remote->version;
			$result->tested         = $remote->tested;
			$result->requires       = $remote->requires;
			$result->author         = '<a href="https://www.fooevents.com">FooEvents</a>';
			$result->author_profile = 'https://www.fooevents.com';
			$result->download_link  = '';
			$result->trunk          = '';
			$result->requires_php   = $remote->requires_php;
			$result->last_updated   = $remote->last_updated;
			$result->sections       = array(
				'changelog' => $remote->changelog,
			);

			return $result;

		}

		return $result;
	}

	/**
	 * Intercept WordPress updater data.
	 *
	 * @since 1.0.2
	 * @param array $transient The current transient data.
	 *
	 * @return array Transient data.
	 */
	public function fooeventspos_set_transitent( $transient ) {

		$this->fooeventspos_init_plugin_data();
		$this->fooeventspos_get_latest_plugin_details();

		if ( isset( $this->fooevents_reponse['update_available'] ) && 'yes' === $this->fooevents_reponse['update_available'] ) {

			$obj              = new stdClass();
			$obj->slug        = $this->slug;
			$obj->new_version = $this->fooevents_reponse['version'];
			$obj->url         = $this->fooevents_reponse['url'];
			$obj->package     = $this->fooevents_reponse['url'];

			$transient->response[ $this->slug ] = $obj;

		}

		return $transient;
	}

	/**
	 * Initialize the plugin data.
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_init_plugin_data() {

		$this->slug        = plugin_basename( $this->config['plugin_file'] );
		$this->plugin_data = get_plugin_data( $this->config['plugin_file'], false, false );
	}

	/**
	 * Get the latest plugin details for FooEvents POS.
	 *
	 * @since 1.0.2
	 */
	private function fooeventspos_get_latest_plugin_details() {

		if ( empty( $this->fooevents_api_key ) ) {

			return;

		}

		if ( ! empty( $this->fooevents_reponse ) ) {

			return;

		}

		if ( empty( $this->plugin_data ) ) {

			$this->plugin_data = get_plugin_data( $this->config['plugin_file'], false, false );

		}

		$url = 'https://www.fooevents.com/?rest_route=/fooevents/check_api';

		$last_update_check    = get_option( '_fooevents_pos_last_update_check', true );
		$last_update_response = get_option( '_fooevents_pos_last_update_response', true );
		$expire_check         = ( strtotime( current_time( 'mysql' ) ) ) - 7200;

		if ( empty( $last_update_check ) || 1 === (int) $last_update_check || $last_update_check <= $expire_check ) {

			$params = array(
				'api'         => $this->fooevents_api_key,
				'envato_api'  => $this->envato_api_key,
				'plugin_name' => $this->plugin_data['Name'],
				'version'     => $this->plugin_data['Version'],
				'home_url'    => $this->home_url,
			);

			$response = wp_remote_post( $url, array( 'body' => $params ) );

			if ( ! is_wp_error( $response ) ) {

				$response                = $response['body'];
				$this->fooevents_reponse = json_decode( $response, true );
				update_option( '_fooevents_pos_last_update_response', $response );

			} else {

				$this->fooevents_reponse = '';

			}

			$timestamp = strtotime( current_time( 'mysql' ) );
			update_option( '_fooevents_pos_last_update_check', time() );

		} else {

			$this->fooevents_reponse = json_decode( $last_update_response, true );

		}
	}

	/**
	 * Show the upgrade notification.
	 *
	 * @since 1.0.2
	 * @param array $current_plugin_metadata The current plugin metadata.
	 * @param array $new_plugin_metadata The new plugin metadata.
	 */
	public function fooeventspos_show_upgrade_notification( $current_plugin_metadata, $new_plugin_metadata ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		if ( empty( $this->fooevents_reponse ) ) {

			$this->fooeventspos_get_latest_plugin_details();

		}

		if ( ! empty( $this->fooevents_reponse ) ) {

			require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

			if ( 'error' === $this->fooevents_reponse['status'] || 'error_license' === $this->fooevents_reponse['status'] ) {

				echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>' . esc_html( $fooeventspos_phrases['notice_fooevents_pos_important_upgrade'] ) . ':</strong> ';
				echo wp_kses_post( $this->fooevents_reponse['message'] );
				echo '</p>';

			}

			if ( 'success' === $this->fooevents_reponse['status'] ) {

				echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>' . esc_html( $fooeventspos_phrases['notice_fooevents_pos_important_upgrade'] ) . ':</strong> ';
				echo esc_html( $fooeventspos_phrases['notice_fooevents_pos_backup_before_upgrade'] );
				echo '</p>';

			}
		}
	}

	/**
	 * Update options after the plugin updates.
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_after_plugin_update() {

		update_option( '_fooevents_pos_last_update_check', '' );
		update_option( '_fooevents_pos_last_update_response', '' );
	}
}
