<?php

namespace KueueEvents\Core\Modules\Frontend;

class EventsController {

    public function run() {
        add_shortcode( 'kq_events', [ $this, 'render_events_list' ] );
        add_shortcode( 'kq_event', [ $this, 'render_single_event' ] );
    }

    public function render_events_list() {
        $events = \KueueEvents\Core\Modules\Events\EventRepository::get_all_active();
        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/events-list.php';
        return ob_get_clean();
    }

    public function render_single_event( $atts ) {
        $a = shortcode_atts( [ 'id' => 0 ], $atts );
        $event_id = $a['id'] ?: get_the_ID();
        
        $event = get_post( $event_id );
        if ( ! $event || $event->post_type !== 'kq_event' ) {
            return '<p>' . __( 'Event not found.', 'kueue-events-core' ) . '</p>';
        }

        $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event( $event_id );

        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/event-single.php';
        return ob_get_clean();
    }
}
