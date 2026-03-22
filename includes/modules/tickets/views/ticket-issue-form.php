<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php _e( 'Issue Manual Ticket', 'kueue-events-core' ); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field( 'kq_issue_manual_ticket_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="event_id"><?php _e( 'Event', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="event_id" id="event_id" required>
                        <option value=""><?php _e( '-- Select Event --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $events as $event ) : ?>
                            <option value="<?php echo (int) $event->ID; ?>"><?php echo esc_html( $event->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="attendee_id"><?php _e( 'Attendee', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="attendee_id" id="attendee_id" required disabled>
                        <option value=""><?php _e( '-- Select Event First --', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ticket_type_id"><?php _e( 'Ticket Type', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="ticket_type_id" id="ticket_type_id" required disabled>
                        <option value=""><?php _e( '-- Select Event First --', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="kq_issue_manual_ticket" id="submit" class="button button-primary" value="<?php _e( 'Issue Ticket', 'kueue-events-core' ); ?>">
        </p>
    </form>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#event_id').on('change', function() {
            var eventId = $(this).val();
            var $attendeeSelect = $('#attendee_id');
            var $ticketTypeSelect = $('#ticket_type_id');
            
            if (!eventId) {
                $attendeeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- Select Event First --', 'kueue-events-core' ); ?></option>');
                $ticketTypeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- Select Event First --', 'kueue-events-core' ); ?></option>');
                return;
            }
            
            // Loading state
            $attendeeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- Loading Attendees --', 'kueue-events-core' ); ?></option>');
            $ticketTypeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- Loading Ticket Types --', 'kueue-events-core' ); ?></option>');
            
            // Get Ticket Types
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'kq_get_ticket_types',
                    event_id: eventId,
                    nonce: '<?php echo wp_create_nonce("kq_get_ticket_types_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var options = '<option value=""><?php _e( '-- Select Ticket Type --', 'kueue-events-core' ); ?></option>';
                        $.each(response.data, function(i, item) {
                            options += '<option value="' + item.id + '">' + item.name + ' (' + item.price + ')</option>';
                        });
                        $ticketTypeSelect.prop('disabled', false).html(options);
                    } else {
                        $ticketTypeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- No ticket types --', 'kueue-events-core' ); ?></option>');
                    }
                }
            });

            // Get Attendees
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'kq_get_attendees',
                    event_id: eventId,
                    nonce: '<?php echo wp_create_nonce("kq_get_attendees_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var options = '<option value=""><?php _e( '-- Select Attendee --', 'kueue-events-core' ); ?></option>';
                        $.each(response.data, function(i, item) {
                            options += '<option value="' + item.id + '">' + item.first_name + ' ' + item.last_name + ' (' + item.email + ')</option>';
                        });
                        $attendeeSelect.prop('disabled', false).html(options);
                    } else {
                        $attendeeSelect.prop('disabled', true).html('<option value=""><?php _e( '-- No attendees found --', 'kueue-events-core' ); ?></option>');
                    }
                }
            });
        });
    });
</script>
