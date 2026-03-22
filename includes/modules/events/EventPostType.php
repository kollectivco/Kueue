<?php

namespace KueueEvents\Core\Modules\Events;

class EventPostType {

    public function run() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_filter( 'manage_kq_event_posts_columns', [ $this, 'set_custom_columns' ] );
        add_action( 'manage_kq_event_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
    }

    public function register_cpt() {
        $labels = [
            'name'               => _x( 'Events', 'post type general name', 'kueue-events-core' ),
            'singular_name'      => _x( 'Event', 'post type singular name', 'kueue-events-core' ),
            'menu_name'          => _x( 'All Events', 'admin menu', 'kueue-events-core' ),
            'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'kueue-events-core' ),
            'add_new'            => _x( 'Add New', 'event', 'kueue-events-core' ),
            'add_new_item'       => __( 'Add New Event', 'kueue-events-core' ),
            'new_item'           => __( 'New Event', 'kueue-events-core' ),
            'edit_item'          => __( 'Edit Event', 'kueue-events-core' ),
            'view_item'          => __( 'View Event', 'kueue-events-core' ),
            'all_items'          => __( 'All Events', 'kueue-events-core' ),
            'search_items'       => __( 'Search Events', 'kueue-events-core' ),
            'not_found'          => __( 'No events found.', 'kueue-events-core' ),
            'not_found_in_trash' => __( 'No events found in Trash.', 'kueue-events-core' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'kq-events-dashboard',
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'kq_event' ],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => [ 'title', 'editor', 'thumbnail' ],
        ];

        register_post_type( 'kq_event', $args );
    }

    public function set_custom_columns( $columns ) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['organizer'] = __( 'Organizer', 'kueue-events-core' );
        $new_columns['event_status'] = __( 'Status', 'kueue-events-core' );
        $new_columns['start_date'] = __( 'Start Date', 'kueue-events-core' );
        $new_columns['venue'] = __( 'Venue', 'kueue-events-core' );
        $new_columns['sales_enabled'] = __( 'Sales Enabled', 'kueue-events-core' );
        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    public function render_custom_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'organizer':
                $organizer_id = get_post_meta( $post_id, '_kq_organizer_id', true );
                $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_id( $organizer_id );
                echo $organizer ? esc_html( $organizer->organizer_name ) : '—';
                break;

            case 'event_status':
                $status = get_post_meta( $post_id, '_kq_event_status', true );
                echo esc_html( ucfirst( $status ) );
                break;

            case 'start_date':
                $start_date = get_post_meta( $post_id, '_kq_start_date', true );
                echo $start_date ? esc_html( $start_date ) : '—';
                break;

            case 'venue':
                $venue_name = get_post_meta( $post_id, '_kq_venue_name', true );
                echo $venue_name ? esc_html( $venue_name ) : '—';
                break;

            case 'sales_enabled':
                $enabled = get_post_meta( $post_id, '_kq_enable_sales', true );
                echo $enabled ? '<span style="color: green;">Yes</span>' : '<span style="color: red;">No</span>';
                break;
        }
    }
}
