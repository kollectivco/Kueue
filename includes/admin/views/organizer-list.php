<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>
        <?php _e( 'Organizers', 'kueue-events-core' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=kq-organizers&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'kueue-events-core' ); ?></a>
    </h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php _e( 'Organizer Name', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-user"><?php _e( 'Linked User', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-email"><?php _e( 'Email', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-commission"><?php _e( 'Commission', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $organizers ) ) : ?>
                <tr><td colspan="6"><?php _e( 'No organizers found.', 'kueue-events-core' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $organizers as $organizer ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $organizer->organizer_name ); ?></strong></td>
                        <td><?php 
                            $user = get_userdata( $organizer->user_id );
                            echo $user ? esc_html( $user->user_login ) . ' (' . $user->ID . ')' : '—';
                        ?></td>
                        <td><?php echo esc_html( $organizer->email ); ?></td>
                        <td>
                            <?php 
                            $status_color = 'gray';
                            if ( $organizer->status === 'active' ) $status_color = 'green';
                            if ( $organizer->status === 'suspended' ) $status_color = 'red';
                            if ( $organizer->status === 'pending' ) $status_color = 'orange';
                            ?>
                            <span style="color: <?php echo $status_color; ?>; font-weight: bold;"><?php echo esc_html( ucfirst( $organizer->status ) ); ?></span>
                        </td>
                        <td><?php echo esc_html( ucfirst( $organizer->commission_type ) ) . ': ' . esc_html( $organizer->commission_value ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=kq-organizers&action=edit&id=' . $organizer->id ); ?>"><?php _e( 'Edit', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-organizers&action=delete&id=' . $organizer->id ), 'kq_delete_organizer_' . $organizer->id ); ?>" style="color: red;" onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
