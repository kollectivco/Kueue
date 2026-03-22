<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="kq-bookings-admin">
    <h1><?php _e( 'Event Dates & Time Slots', 'kueue-events-core' ); ?></h1>
    
    <style>
        .bookings-grid { display: grid; grid-template-columns: 1fr 350px; gap: 20px; margin-top: 20px; }
        .slot-card { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .slot-meta { font-size: 0.9em; color: #666; }
        .slot-capacity { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #eef7ff; font-weight: bold; }
        
        .add-date-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; position: sticky; top: 100px; }
        .form-row { margin-bottom: 15px; }
        .form-row label { display: block; font-weight: bold; margin-bottom: 5px; }
    </style>

    <div class="bookings-grid">
        <div class="bookings-main">
            <h3><?php _e( 'Configure Slots for:', 'kueue-events-core' ); ?> 
                <select id="event-selector">
                    <option value=""><?php _e( '-- Choose Event --', 'kueue-events-core' ); ?></option>
                    <?php foreach ( $events as $event ) : ?>
                        <option value="<?php echo (int) $event->ID; ?>"><?php echo esc_html( $event->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </h3>

            <div id="slots-list">
                <p><?php _e( 'Select an event to manage its dates and time slots.', 'kueue-events-core' ); ?></p>
            </div>
        </div>

        <div class="bookings-sidebar">
            <div class="add-date-card" id="add-slot-form" style="display:none;">
                <h3><?php _e( 'Add New Slot', 'kueue-events-core' ); ?></h3>
                <div class="form-row">
                    <label><?php _e( 'Date', 'kueue-events-core' ); ?></label>
                    <input type="date" id="new-slot-date" class="widefat">
                </div>
                <div class="form-row">
                    <label><?php _e( 'Slot Label / Time', 'kueue-events-core' ); ?></label>
                    <input type="text" id="new-slot-label" class="widefat" placeholder="08:00 PM">
                </div>
                <div class="form-row">
                    <label><?php _e( 'Capacity', 'kueue-events-core' ); ?></label>
                    <input type="number" id="new-slot-capacity" class="widefat" value="100">
                </div>
                <button class="button button-primary" id="save-slot-btn"><?php _e( 'Add Slot', 'kueue-events-core' ); ?></button>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const restUrl = '<?php echo esc_url_raw( rest_url( "kq/v1/bookings" ) ); ?>';
        const nonce = '<?php echo wp_create_nonce( "wp_rest" ); ?>';

        $('#event-selector').on('change', function() {
            const eventId = $(this).val();
            if (!eventId) {
                $('#slots-list').html('<p>Please select an event.</p>');
                $('#add-slot-form').hide();
                return;
            }

            fetchSlots(eventId);
            $('#add-slot-form').show();
        });

        function fetchSlots(eventId) {
            $('#slots-list').html('Loading...');
            $.ajax({
                url: restUrl + '/slots?event_id=' + eventId,
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                success: function(response) {
                    renderSlots(response);
                }
            });
        }

        function renderSlots(slots) {
            const container = $('#slots-list');
            container.empty();
            if (slots.length === 0) {
                container.html('<p>No slots defined for this event yet.</p>');
                return;
            }

            slots.forEach(slot => {
                container.append(`
                    <div class="slot-card">
                        <div>
                            <strong>${slot.date}</strong> - ${slot.label}
                            <div class="slot-meta">Capacity: <span class="slot-capacity">${slot.sold_count} / ${slot.capacity}</span></div>
                        </div>
                        <button class="button button-link-delete delete-slot" data-id="${slot.id}">Delete</button>
                    </div>
                `);
            });
        }

        $('#save-slot-btn').on('click', function() {
            const eventId = $('#event-selector').val();
            const data = {
                event_id: eventId,
                date: $('#new-slot-date').val(),
                label: $('#new-slot-label').val(),
                capacity: $('#new-slot-capacity').val(),
                organizer_id: 1 // Default Admin for now
            };

            $.ajax({
                url: restUrl + '/slots',
                method: 'POST',
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function() {
                    fetchSlots(eventId);
                    $('#new-slot-label').val('');
                }
            });
        });
    });
    </script>
</div>
