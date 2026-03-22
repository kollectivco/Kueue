<?php

namespace KueueEvents\Core\Modules\Checkins;

class CheckinController extends \WP_REST_Controller {

    protected $namespace = 'kq/v1';
    protected $rest_base = 'validate-ticket';

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'validate_ticket' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'token' => [
                        'required' => true,
                        'type'     => 'string',
                    ],
                    'event_id' => [
                        'required' => false,
                        'type'     => 'integer',
                    ],
                    'mode' => [
                        'required' => false,
                        'type'     => 'string',
                        'enum'     => [ 'auto', 'checkin', 'checkout' ],
                        'default'  => 'auto',
                    ],
                ],
            ],
        ] );

        register_rest_route( $this->namespace, '/export-tickets', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'export_tickets' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'event_id' => [
                        'required' => true,
                        'type'     => 'integer',
                    ],
                ],
            ],
        ] );

        register_rest_route( $this->namespace, '/sync-scans', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'sync_scans' ],
                'permission_callback' => [ $this, 'permissions_check' ],
                'args'                => [
                    'scans' => [
                        'required' => true,
                        'type'     => 'array',
                    ],
                ],
            ],
        ] );
    }

    /**
     * Permission check for scanner validation.
     */
    public function permissions_check( $request ) {
        $token = $request->get_header( 'X-Scanner-Token' );
        $device_id = $request->get_header( 'X-Device-ID' );

        if ( ! $token || ! $device_id ) {
            return new \WP_Error( 'rest_forbidden', __( 'Missing Scanner Identity Headers.', 'kueue-events-core' ), [ 'status' => 401 ] );
        }

        $session = ScannerSessionManager::validate_session( $token, $device_id );
        if ( ! $session ) {
            return new \WP_Error( 'rest_forbidden', __( 'Invalid or Expired Scanner Session.', 'kueue-events-core' ), [ 'status' => 401 ] );
        }

        // Assign user_id for the callback
        set_current_user( $session->user_id );

        return true;
    }

    /**
     * Validate ticket callback.
     */
    public function validate_ticket( $request ) {
        $token = $request->get_param( 'token' );
        $event_id = $request->get_param( 'event_id' );
        $mode = $request->get_param( 'mode' );
        $user_id = get_current_user_id();

        // Simple API Rate Limiting (per user)
        if ( $this->is_rate_limited( $user_id ) ) {
            return new \WP_REST_Response( [ 'status' => 'rate_limited', 'message' => __( 'Too many requests. Slow down.', 'kueue-events-core' ) ], 429 );
        }

        $result = CheckinService::process_scan( $token, $user_id, [
            'event_id' => $event_id,
            'mode'     => $mode
        ] );

        return new \WP_REST_Response( $result, 200 );
    }

    /**
     * Batch Sync scans from offline mode.
     */
    public function sync_scans( $request ) {
        $scans = (array) $request->get_param( 'scans' );
        $user_id = get_current_user_id();

        $results = [];
        foreach ( $scans as $scan ) {
            $token = $scan['token'] ?? '';
            $event_id = $scan['event_id'] ?? 0;
            $mode = $scan['mode'] ?? 'auto';
            $offline_at = $scan['offline_at'] ?? '';

            $result = CheckinService::process_scan( $token, $user_id, [
                'event_id' => $event_id,
                'mode'     => $mode,
                'note'     => "Offline Scan at $offline_at"
            ] );

            $results[] = [
                'token'  => $token,
                'status' => $result['status'],
                'msg'    => $result['message']
            ];
        }

        return new \WP_REST_Response( [ 'results' => $results ], 200 );
    }

    private function is_rate_limited( $user_id ) {
        $transient_key = 'kq_api_rate_limit_' . $user_id;
        $count = (int) get_transient( $transient_key );
        
        if ( $count > 60 ) { // 60 requests per minute
            return true;
        }

        set_transient( $transient_key, $count + 1, 60 );
        return false;
    }

    /**
     * Export ticket data for offline caching.
     */
    public function export_tickets( $request ) {
        $event_id = $request->get_param( 'event_id' );
        global $wpdb;
        $tickets_table = $wpdb->prefix . 'kq_tickets';
        $attendees_table = $wpdb->prefix . 'kq_attendees';

        // Fetch minimal data needed for offline validation
        $data = $wpdb->get_results( $wpdb->prepare(
            "SELECT t.secure_token as token, t.event_id, t.checkin_status as status, CONCAT(a.first_name, ' ', a.last_name) as attendee_name 
             FROM $tickets_table t 
             JOIN $attendees_table a ON t.attendee_id = a.id
             WHERE t.event_id = %d AND t.ticket_status = 'active'",
            $event_id
        ) );

        return new \WP_REST_Response( $data, 200 );
    }
}
