<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$is_edit = ( $action === 'edit' );
$att_data = [
    'first_name'     => '',
    'last_name'      => '',
    'email'          => '',
    'phone'          => '',
    'event_id'       => 0,
    'ticket_type_id' => 0,
    'status'         => 'confirmed',
    'company'        => '',
    'designation'    => '',
];

if ( $is_edit && isset( $attendee ) && is_object( $attendee ) ) {
    $att_data['first_name']     = isset($attendee->first_name) ? $attendee->first_name : '';
    $att_data['last_name']      = isset($attendee->last_name) ? $attendee->last_name : '';
    $att_data['email']          = isset($attendee->email) ? $attendee->email : '';
    $att_data['phone']          = isset($attendee->phone) ? $attendee->phone : '';
    $att_data['event_id']       = isset($attendee->event_id) ? $attendee->event_id : 0;
    $att_data['ticket_type_id'] = isset($attendee->ticket_type_id) ? $attendee->ticket_type_id : 0;
    $att_data['status']         = isset($attendee->status) ? $attendee->status : 'confirmed';
    $att_data['company']        = isset($attendee->company) ? $attendee->company : '';
    $att_data['designation']    = isset($attendee->designation) ? $attendee->designation : '';
}
?>
<div class="wrap">
    <h1><?php echo $is_edit ? __( 'Edit Attendee', 'kueue-events-core' ) : __( 'Add New Attendee', 'kueue-events-core' ); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field( 'kq_save_attendee_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="event_id"><?php _e( 'Event', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="event_id" id="event_id" required>
                        <option value=""><?php _e( '-- Select Event --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $events as $event ) : ?>
                            <option value="<?php echo (int) $event->ID; ?>" <?php selected( $att_data['event_id'], $event->ID ); ?>><?php echo esc_html( $event->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ticket_type_id"><?php _e( 'Ticket Type', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="ticket_type_id" id="ticket_type_id">
                        <option value=""><?php _e( '-- Select Ticket Type --', 'kueue-events-core' ); ?></option>
                        <?php 
                            if ( $att_data['event_id'] ) {
                                $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $att_data['event_id'] );
                                foreach($ticket_types as $tt) {
                                    echo '<option value="'.(int)$tt->id.'" '.selected($att_data['ticket_type_id'], $tt->id, false).'>'.esc_html($tt->name).'</option>';
                                }
                            }
                        ?>
                    </select>
                    <p class="description"><?php _e('Only shows if event is selected.', 'kueue-events-core'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="first_name"><?php _e( 'First Name', 'kueue-events-core' ); ?></label></th>
                <td><input name="first_name" type="text" id="first_name" value="<?php echo esc_attr( $att_data['first_name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="last_name"><?php _e( 'Last Name', 'kueue-events-core' ); ?></label></th>
                <td><input name="last_name" type="text" id="last_name" value="<?php echo esc_attr( $att_data['last_name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="email"><?php _e( 'Email', 'kueue-events-core' ); ?></label></th>
                <td><input name="email" type="email" id="email" value="<?php echo esc_attr( $att_data['email'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="phone"><?php _e( 'Phone', 'kueue-events-core' ); ?></label></th>
                <td><input name="phone" type="text" id="phone" value="<?php echo esc_attr( $att_data['phone'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="company"><?php _e( 'Company', 'kueue-events-core' ); ?></label></th>
                <td><input name="company" type="text" id="company" value="<?php echo esc_attr( $att_data['company'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="designation"><?php _e( 'Designation', 'kueue-events-core' ); ?></label></th>
                <td><input name="designation" type="text" id="designation" value="<?php echo esc_attr( $att_data['designation'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="status"><?php _e( 'Status', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="status" id="status" class="regular-text">
                        <option value="pending" <?php selected( $att_data['status'], 'pending' ); ?>><?php _e( 'Pending', 'kueue-events-core' ); ?></option>
                        <option value="confirmed" <?php selected( $att_data['status'], 'confirmed' ); ?>><?php _e( 'Confirmed', 'kueue-events-core' ); ?></option>
                        <option value="cancelled" <?php selected( $att_data['status'], 'cancelled' ); ?>><?php _e( 'Cancelled', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="kq_save_attendee" id="submit" class="button button-primary" value="<?php _e( 'Save Attendee', 'kueue-events-core' ); ?>">
        </p>
    </form>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#event_id').on('change', function() {
            var eventId = $(this).val();
            var $ticketTypeSelect = $('#ticket_type_id');
            
            $ticketTypeSelect.html('<option value=""><?php _e( '-- Loading --', 'kueue-events-core' ); ?></option>');
            
            if (!eventId) {
                $ticketTypeSelect.html('<option value=""><?php _e( '-- Select Event First --', 'kueue-events-core' ); ?></option>');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'kq_get_ticket_types',
                    event_id: eventId,
                    nonce: '<?php echo wp_create_nonce("kq_get_ticket_types_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value=""><?php _e( '-- Select Ticket Type --', 'kueue-events-core' ); ?></option>';
                        $.each(response.data, function(i, item) {
                            options += '<option value="' + item.id + '">' + item.name + '</option>';
                        });
                        $ticketTypeSelect.html(options);
                    } else {
                        $ticketTypeSelect.html('<option value=""><?php _e( '-- None Found --', 'kueue-events-core' ); ?></option>');
                    }
                }
            });
        });
    });
</script>
