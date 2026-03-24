<?php

namespace KueueEvents\Core\Core;

class Activator {

    /**
     * Activate the plugin.
     * Creates custom tables and sets up roles.
     */
    public function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // 1) wp_kq_gateway_accounts
        $table_gateway_accounts = $wpdb->prefix . 'kq_gateway_accounts';
        $sql1 = "CREATE TABLE $table_gateway_accounts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            channel varchar(50) NOT NULL,
            provider varchar(100) NOT NULL,
            account_name varchar(255) NOT NULL,
            is_enabled tinyint(1) DEFAULT 1 NOT NULL,
            is_default tinyint(1) DEFAULT 0 NOT NULL,
            country_code varchar(5) NULL,
            organizer_id bigint(20) NULL,
            config_json longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // 2) wp_kq_delivery_logs
        $table_delivery_logs = $wpdb->prefix . 'kq_delivery_logs';
        $sql2 = "CREATE TABLE $table_delivery_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            channel varchar(50) NOT NULL,
            gateway_account_id bigint(20) NOT NULL,
            recipient varchar(100) NOT NULL,
            payload_summary text NOT NULL,
            status varchar(50) NOT NULL,
            response_code varchar(20) NULL,
            response_body longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY channel (channel),
            KEY status (status),
            KEY recipient (recipient)
        ) $charset_collate;";

        // 3) wp_kq_delivery_queue
        $table_delivery_queue = $wpdb->prefix . 'kq_delivery_queue';
        $sql3 = "CREATE TABLE $table_delivery_queue (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            channel varchar(50) NOT NULL,
            gateway_account_id bigint(20) NOT NULL,
            payload_json longtext NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            retry_count int(11) DEFAULT 0 NOT NULL,
            scheduled_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            processed_at datetime NULL,
            PRIMARY KEY  (id),
            KEY channel (channel),
            KEY status (status),
            KEY scheduled_at (scheduled_at)
        ) $charset_collate;";

        // 4) wp_kq_organizers
        $table_organizers = $wpdb->prefix . 'kq_organizers';
        $sql4 = "CREATE TABLE $table_organizers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            organizer_name varchar(255) NOT NULL,
            organizer_slug varchar(255) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50) NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            logo_id bigint(20) NULL,
            cover_id bigint(20) NULL,
            commission_type varchar(20) DEFAULT 'percentage' NOT NULL,
            commission_value decimal(10,2) DEFAULT 0.00 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        // 5) wp_kq_ticket_types
        $table_ticket_types = $wpdb->prefix . 'kq_ticket_types';
        $sql5 = "CREATE TABLE $table_ticket_types (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text NULL,
            type_code varchar(50) NULL,
            price decimal(10,2) DEFAULT 0.00 NOT NULL,
            sale_price decimal(10,2) NULL,
            currency varchar(10) DEFAULT 'EGP' NOT NULL,
            stock_quantity int(11) DEFAULT 0 NOT NULL,
            sold_quantity int(11) DEFAULT 0 NOT NULL,
            reserved_quantity int(11) DEFAULT 0 NOT NULL,
            min_per_order int(11) DEFAULT 1 NOT NULL,
            max_per_order int(11) DEFAULT 10 NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            sort_order int(11) DEFAULT 0 NOT NULL,
            meta_json longtext NULL,
            wc_product_id bigint(20) NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id)
        ) $charset_collate;";

        // 6) wp_kq_attendees
        $table_attendees = $wpdb->prefix . 'kq_attendees';
        $sql6 = "CREATE TABLE $table_attendees (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            organizer_id bigint(20) NOT NULL,
            ticket_type_id bigint(20) NULL,
            order_id bigint(20) NULL,
            order_item_id bigint(20) NULL,
            user_id bigint(20) NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50) NULL,
            company varchar(255) NULL,
            designation varchar(255) NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            source varchar(20) DEFAULT 'order' NOT NULL,
            meta_json longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id),
            KEY organizer_id (organizer_id),
            KEY ticket_type_id (ticket_type_id)
        ) $charset_collate;";

        // 7) wp_kq_tickets
        $table_tickets = $wpdb->prefix . 'kq_tickets';
        $sql7 = "CREATE TABLE $table_tickets (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            organizer_id bigint(20) NOT NULL,
            attendee_id bigint(20) NOT NULL,
            ticket_type_id bigint(20) NULL,
            order_id bigint(20) NULL,
            order_item_id bigint(20) NULL,
            ticket_number varchar(100) NOT NULL,
            secure_token varchar(255) NOT NULL,
            delivery_status varchar(20) DEFAULT 'not_sent' NOT NULL,
            ticket_status varchar(20) DEFAULT 'active' NOT NULL,
            checkin_status varchar(20) DEFAULT 'not_checked_in' NOT NULL,
            booking_date_id bigint(20) NULL,
            booking_slot_id bigint(20) NULL,
            seating_map_id bigint(20) NULL,
            section_id bigint(20) NULL,
            row_id bigint(20) NULL,
            seat_id bigint(20) NULL,
            seat_label varchar(50) NULL,
            issued_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            expires_at datetime NULL,
            last_checkin_at datetime NULL,
            meta_json longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY ticket_number (ticket_number),
            UNIQUE KEY secure_token (secure_token),
            KEY event_id (event_id),
            KEY organizer_id (organizer_id),
            KEY attendee_id (attendee_id),
            KEY order_id (order_id),
            KEY ticket_status (ticket_status),
            KEY booking_slot_id (booking_slot_id),
            KEY seat_id (seat_id)
        ) $charset_collate;";

        // 8) wp_kq_checkins
        $table_checkins = $wpdb->prefix . 'kq_checkins';
        $sql8 = "CREATE TABLE $table_checkins (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) NOT NULL,
            event_id bigint(20) NOT NULL,
            organizer_id bigint(20) NOT NULL,
            scanned_by_user_id bigint(20) NOT NULL,
            scan_type varchar(20) NOT NULL,
            device_info text NULL,
            result_status varchar(50) NOT NULL,
            note text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY ticket_id (ticket_id),
            KEY event_id (event_id),
            KEY organizer_id (organizer_id),
            KEY scanned_by_user_id (scanned_by_user_id)
        ) $charset_collate;";

        // 9) wp_kq_scanner_sessions
        $table_scanner_sessions = $wpdb->prefix . 'kq_scanner_sessions';
        $sql9 = "CREATE TABLE $table_scanner_sessions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            session_token varchar(255) NOT NULL,
            device_id varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            expires_at datetime NOT NULL,
            last_activity_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY session_token (session_token),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql1 );
        dbDelta( $sql2 );
        dbDelta( $sql3 );
        dbDelta( $sql4 );
        dbDelta( $sql5 );
        dbDelta( $sql6 );
        dbDelta( $sql7 );
        dbDelta( $sql8 );
        dbDelta( $sql9 );

        // 10) wp_kq_booking_dates
        $table_booking_dates = $wpdb->prefix . 'kq_booking_dates';
        $sql10 = "CREATE TABLE $table_booking_dates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            event_date date NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id)
        ) $charset_collate;";

        // 11) wp_kq_booking_slots
        $table_booking_slots = $wpdb->prefix . 'kq_booking_slots';
        $sql11 = "CREATE TABLE $table_booking_slots (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            booking_date_id bigint(20) NOT NULL,
            slot_label varchar(255) NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            capacity int(11) DEFAULT 0 NOT NULL,
            reserved_count int(11) DEFAULT 0 NOT NULL,
            sold_count int(11) DEFAULT 0 NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            PRIMARY KEY  (id),
            KEY booking_date_id (booking_date_id)
        ) $charset_collate;";

        // 12) wp_kq_seating_maps
        $table_seating_maps = $wpdb->prefix . 'kq_seating_maps';
        $sql12 = "CREATE TABLE $table_seating_maps (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY event_id (event_id)
        ) $charset_collate;";

        // 13) wp_kq_seating_sections
        $table_seating_sections = $wpdb->prefix . 'kq_seating_sections';
        $sql13 = "CREATE TABLE $table_seating_sections (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            map_id bigint(20) NOT NULL,
            event_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            price_modifier decimal(10,2) DEFAULT 0.00 NOT NULL,
            sort_order int(11) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY map_id (map_id)
        ) $charset_collate;";

        // 14) wp_kq_seating_rows
        $table_seating_rows = $wpdb->prefix . 'kq_seating_rows';
        $sql14 = "CREATE TABLE $table_seating_rows (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            section_id bigint(20) NOT NULL,
            row_label varchar(50) NOT NULL,
            sort_order int(11) DEFAULT 0 NOT NULL,
            PRIMARY KEY  (id),
            KEY section_id (section_id)
        ) $charset_collate;";

        // 15) wp_kq_seating_seats
        $table_seating_seats = $wpdb->prefix . 'kq_seating_seats';
        $sql15 = "CREATE TABLE $table_seating_seats (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            map_id bigint(20) NOT NULL,
            section_id bigint(20) NOT NULL,
            row_id bigint(20) NOT NULL,
            seat_label varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'available' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY row_id (row_id),
            KEY map_id (map_id)
        ) $charset_collate;";

        // 16) wp_kq_commissions
        $table_commissions = $wpdb->prefix . 'kq_commissions';
        $sql16 = "CREATE TABLE $table_commissions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            organizer_id bigint(20) NOT NULL,
            event_id bigint(20) NOT NULL,
            order_id bigint(20) NULL,
            gross_amount decimal(10,2) NOT NULL,
            commission_amount decimal(10,2) NOT NULL,
            net_amount decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'unpaid' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY organizer_id (organizer_id),
            KEY event_id (event_id)
        ) $charset_collate;";

        // 17) wp_kq_payouts
        $table_payouts = $wpdb->prefix . 'kq_payouts';
        $sql17 = "CREATE TABLE $table_payouts (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            organizer_id bigint(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            payment_method varchar(100) NULL,
            notes text NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY organizer_id (organizer_id)
        ) $charset_collate;";

        dbDelta( $sql10 );
        dbDelta( $sql11 );
        dbDelta( $sql12 );
        dbDelta( $sql13 );
        dbDelta( $sql14 );
        dbDelta( $sql15 );
        dbDelta( $sql16 );
        dbDelta( $sql17 );

        // Create the role
        $this->create_roles();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Set up roles and capabilities.
     */
    private function create_roles() {
        add_role( 'kq_organizer', 'Kueue Organizer', [
            'read'               => true,
            'manage_kq_events'   => true,
            'manage_kq_tickets'  => true,
            'manage_kq_bookings' => true,
            'manage_kq_reports'  => true,
        ] );

        // Add capabilities to admin
        $admin = get_role( 'administrator' );
        if ( $admin ) {
            $admin->add_cap( 'manage_kq_events' );
            $admin->add_cap( 'manage_kq_tickets' );
            $admin->add_cap( 'manage_kq_bookings' );
            $admin->add_cap( 'manage_kq_reports' );
        }
    }
}
