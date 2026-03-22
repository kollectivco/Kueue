<?php

namespace KueueEvents\Core\Modules\Vendors;

class OrganizerAdmin {

    public function run() {
        // No action/filter registration here for now if called by AdminController
    }

    public function render_list() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';

        if ( 'delete' === $action && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_delete_organizer_' . $_GET['id'] );
            OrganizerRepository::delete( $_GET['id'] );
            wp_redirect( admin_url( 'admin.php?page=kq-organizers' ) );
            exit;
        }

        if ( 'edit' === $action || 'add' === $action ) {
            $this->render_form( $action );
            return;
        }

        $organizers = OrganizerRepository::get_all();
        include_once KQ_PLUGIN_DIR . 'includes/admin/views/organizer-list.php';
    }

    private function render_form( $action ) {
        $id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        $organizer = null;

        if ( $id ) {
            $organizer = OrganizerRepository::get_by_id( $id );
        }

        if ( isset( $_POST['kq_save_organizer'] ) ) {
            check_admin_referer( 'kq_save_organizer_nonce' );
            $this->handle_save( $id );
            return;
        }

        $all_users = get_users( [ 'fields' => [ 'ID', 'user_login', 'display_name' ] ] );
        include_once KQ_PLUGIN_DIR . 'includes/admin/views/organizer-form.php';
    }

    private function handle_save( $id ) {
        $user_id = (int) $_POST['user_id'];
        $organizer_name = sanitize_text_field( $_POST['organizer_name'] );
        $organizer_slug = sanitize_title_with_dashes( $organizer_name );
        $email = sanitize_email( $_POST['email'] );
        $phone = sanitize_text_field( $_POST['phone'] ?? '' );
        $status = sanitize_text_field( $_POST['status'] );
        $commission_type = sanitize_text_field( $_POST['commission_type'] );
        $commission_value = (float) $_POST['commission_value'];

        $data = [
            'user_id'          => $user_id,
            'organizer_name'   => $organizer_name,
            'organizer_slug'   => $organizer_slug,
            'email'            => $email,
            'phone'            => $phone,
            'status'           => $status,
            'commission_type'  => $commission_type,
            'commission_value' => $commission_value
        ];

        OrganizerRepository::save( $data, $id );

        wp_redirect( admin_url( 'admin.php?page=kq-organizers' ) );
        exit;
    }
}
