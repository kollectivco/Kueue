<?php

namespace KueueEvents\Core\Modules\Venues;

class VenuePostType {

    public function run() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_venue_meta_boxes' ] );
        add_action( 'save_post_kq_venue', [ $this, 'save_venue_meta' ] );
    }

    public function register_cpt() {
        $labels = [
            'name'               => _x( 'Venues', 'post type general name', 'kueue-events-core' ),
            'singular_name'      => _x( 'Venue', 'post type singular name', 'kueue-events-core' ),
            'menu_name'          => _x( 'Venues', 'admin menu', 'kueue-events-core' ),
            'add_new'            => _x( 'Add New', 'venue', 'kueue-events-core' ),
            'add_new_item'       => __( 'Add New Venue', 'kueue-events-core' ),
            'edit_item'          => __( 'Edit Venue', 'kueue-events-core' ),
            'view_item'          => __( 'View Venue', 'kueue-events-core' ),
            'all_items'          => __( 'All Venues', 'kueue-events-core' ),
            'search_items'       => __( 'Search Venues', 'kueue-events-core' ),
            'not_found'          => __( 'No venues found.', 'kueue-events-core' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'kq-events-dashboard',
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'venues', 'with_front' => false ],
            'capability_type'    => 'post',
            'has_archive'        => 'venues',
            'hierarchical'       => false,
            'supports'           => [ 'title', 'editor', 'thumbnail' ],
            'show_in_rest'       => true,
        ];

        register_post_type( 'kq_venue', $args );
    }

    public function add_venue_meta_boxes() {
        add_meta_box(
            'kq_venue_details',
            __( 'Venue Details', 'kueue-events-core' ),
            [ $this, 'render_venue_meta_box' ],
            'kq_venue',
            'normal',
            'high'
        );
    }

    public function render_venue_meta_box( $post ) {
        wp_nonce_field( 'kq_save_venue_meta', 'kq_venue_nonce' );

        $address = get_post_meta( $post->ID, '_kq_venue_address', true );
        $city = get_post_meta( $post->ID, '_kq_venue_city', true );
        $country = get_post_meta( $post->ID, '_kq_venue_country', true );
        $gmaps_url = get_post_meta( $post->ID, '_kq_venue_gmaps_url', true );
        $lat = get_post_meta( $post->ID, '_kq_venue_lat', true );
        $lng = get_post_meta( $post->ID, '_kq_venue_lng', true );
        ?>
        <div class="kq-admin-field" style="margin-bottom: 15px;">
            <label style="display:block; font-weight:bold;"><?php _e( 'Full Address', 'kueue-events-core' ); ?></label>
            <input type="text" name="venue_address" value="<?php echo esc_attr( $address ); ?>" style="width:100%;">
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div class="kq-admin-field">
                <label style="display:block; font-weight:bold;"><?php _e( 'City', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_city" value="<?php echo esc_attr( $city ); ?>" style="width:100%;">
            </div>
            <div class="kq-admin-field">
                <label style="display:block; font-weight:bold;"><?php _e( 'Country', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_country" value="<?php echo esc_attr( $country ); ?>" style="width:100%;">
            </div>
        </div>
        <div class="kq-admin-field" style="margin-top: 15px;">
            <label style="display:block; font-weight:bold;"><?php _e( 'Google Maps URL', 'kueue-events-core' ); ?></label>
            <input type="text" name="venue_gmaps_url" value="<?php echo esc_attr( $gmaps_url ); ?>" style="width:100%;">
        </div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
            <div class="kq-admin-field">
                <label style="display:block; font-weight:bold;"><?php _e( 'Latitude', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_lat" value="<?php echo esc_attr( $lat ); ?>" style="width:100%;">
            </div>
            <div class="kq-admin-field">
                <label style="display:block; font-weight:bold;"><?php _e( 'Longitude', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_lng" value="<?php echo esc_attr( $lng ); ?>" style="width:100%;">
            </div>
        </div>
        <?php
    }

    public function save_venue_meta( $post_id ) {
        if ( ! isset( $_POST['kq_venue_nonce'] ) || ! wp_verify_nonce( $_POST['kq_venue_nonce'], 'kq_save_venue_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        $fields = [
            'venue_address', 'venue_city', 'venue_country',
            'venue_gmaps_url', 'venue_lat', 'venue_lng'
        ];

        foreach ( $fields as $field ) {
            if ( isset( $_POST[$field] ) ) {
                update_post_meta( $post_id, '_kq_' . $field, sanitize_text_field( $_POST[$field] ) );
            }
        }
    }
}
