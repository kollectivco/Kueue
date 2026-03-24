<?php

namespace KueueEvents\Core\Modules\Dashboard;

class FrontendDashboard {

    public function run() {
        add_shortcode( 'kq_dashboard', [ $this, 'render_dashboard' ] );
    }

    public function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'Please log in to view your dashboard.', 'kueue-events-core' ) . '</p>';
        }

        $user_id = get_current_user_id();
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );

        if ( ! $organizer ) {
            return '<p>' . __( 'Organizer profile not found.', 'kueue-events-core' ) . '</p>';
        }

        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary( $organizer->id );
        $events = get_posts( [
            'post_type'  => 'kq_event',
            'meta_key'   => '_kq_organizer_id',
            'meta_value' => $organizer->id,
            'numberposts' => -1
        ] );

        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Dashboard/views/dashboard-view.php';
        return ob_get_clean();
    }
}
