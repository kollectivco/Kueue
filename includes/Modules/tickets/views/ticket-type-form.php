<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$is_edit = ( $action === 'edit' );
$tt_data = [
    'name'           => '',
    'description'    => '',
    'event_id'       => 0,
    'price'          => 0,
    'sale_price'     => '',
    'stock_quantity' => 0,
    'min_per_order'  => 1,
    'max_per_order'  => 10,
    'status'         => 'active',
    'sort_order'     => 0,
];

if ( $is_edit && isset( $ticket_type ) && is_object( $ticket_type ) ) {
    $tt_data['name']           = isset($ticket_type->name) ? $ticket_type->name : '';
    $tt_data['description']    = isset($ticket_type->description) ? $ticket_type->description : '';
    $tt_data['event_id']       = isset($ticket_type->event_id) ? $ticket_type->event_id : 0;
    $tt_data['price']          = isset($ticket_type->price) ? $ticket_type->price : 0;
    $tt_data['sale_price']     = isset($ticket_type->sale_price) ? $ticket_type->sale_price : '';
    $tt_data['stock_quantity'] = isset($ticket_type->stock_quantity) ? (int) $ticket_type->stock_quantity : 0;
    $tt_data['min_per_order']  = isset($ticket_type->min_per_order) ? (int) $ticket_type->min_per_order : 1;
    $tt_data['max_per_order']  = isset($ticket_type->max_per_order) ? (int) $ticket_type->max_per_order : 10;
    $tt_data['status']         = isset($ticket_type->status) ? $ticket_type->status : 'active';
    $tt_data['sort_order']     = isset($ticket_type->sort_order) ? (int) $ticket_type->sort_order : 0;
}
?>
<div class="wrap">
    <h1><?php echo $is_edit ? __( 'Edit Ticket Type', 'kueue-events-core' ) : __( 'Add New Ticket Type', 'kueue-events-core' ); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field( 'kq_save_ticket_type_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="event_id"><?php _e( 'Event', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="event_id" id="event_id" required>
                        <option value=""><?php _e( '-- Select Event --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $events as $event ) : ?>
                            <option value="<?php echo (int) $event->ID; ?>" <?php selected( $tt_data['event_id'], $event->ID ); ?>><?php echo esc_html( $event->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="name"><?php _e( 'Name', 'kueue-events-core' ); ?></label></th>
                <td><input name="name" type="text" id="name" value="<?php echo esc_attr( $tt_data['name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="description"><?php _e( 'Description', 'kueue-events-core' ); ?></label></th>
                <td><textarea name="description" id="description" class="regular-text" rows="3"><?php echo esc_textarea( $tt_data['description'] ); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="price"><?php _e( 'Price', 'kueue-events-core' ); ?></label></th>
                <td><input name="price" type="number" step="0.01" id="price" value="<?php echo esc_attr( $tt_data['price'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="sale_price"><?php _e( 'Sale Price', 'kueue-events-core' ); ?></label></th>
                <td><input name="sale_price" type="number" step="0.01" id="sale_price" value="<?php echo esc_attr( $tt_data['sale_price'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="stock_quantity"><?php _e( 'Stock Quantity', 'kueue-events-core' ); ?></label></th>
                <td><input name="stock_quantity" type="number" id="stock_quantity" value="<?php echo esc_attr( $tt_data['stock_quantity'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Order Restrictions', 'kueue-events-core' ); ?></th>
                <td>
                    <label><?php _e( 'Min:', 'kueue-events-core' ); ?> <input type="number" name="min_per_order" value="<?php echo esc_attr($tt_data['min_per_order']); ?>" style="width: 60px;"></label>
                    <label><?php _e( 'Max:', 'kueue-events-core' ); ?> <input type="number" name="max_per_order" value="<?php echo esc_attr($tt_data['max_per_order']); ?>" style="width: 60px;"></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="status"><?php _e( 'Status', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="status" id="status" class="regular-text">
                        <option value="active" <?php selected( $tt_data['status'], 'active' ); ?>><?php _e( 'Active', 'kueue-events-core' ); ?></option>
                        <option value="inactive" <?php selected( $tt_data['status'], 'inactive' ); ?>><?php _e( 'Inactive', 'kueue-events-core' ); ?></option>
                        <option value="sold_out" <?php selected( $tt_data['status'], 'sold_out' ); ?>><?php _e( 'Sold Out', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sort_order"><?php _e( 'Sort Order', 'kueue-events-core' ); ?></label></th>
                <td><input name="sort_order" type="number" id="sort_order" value="<?php echo esc_attr( $tt_data['sort_order'] ); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="kq_save_ticket_type" id="submit" class="button button-primary" value="<?php _e( 'Save Ticket Type', 'kueue-events-core' ); ?>">
        </p>
    </form>
</div>
