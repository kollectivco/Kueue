<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="kq-pos-container">
    <h1><?php _e( 'POS / Box Office', 'kueue-events-core' ); ?></h1>
    
    <style>
        .pos-grid { display: grid; grid-template-columns: 1fr 350px; gap: 20px; margin-top: 20px; }
        .pos-card { background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 8px; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .event-selector-card { margin-bottom: 20px; }
        .ticket-type-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
        .ticket-card { border: 2px solid #eee; padding: 15px; border-radius: 10px; cursor: pointer; transition: all 0.2s; }
        .ticket-card:hover { border-color: #0073aa; background: #f0f6fb; }
        .ticket-card.active { border-color: #0073aa; background: #f0f6fb; box-shadow: 0 0 0 1px #0073aa; }
        .ticket-card h3 { margin: 0 0 10px; font-size: 1.1em; }
        .ticket-price { font-weight: bold; color: #d63638; font-size: 1.2em; }
        
        .attendee-form h3 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
        .form-group input { width: 100%; }

        .summary-card { position: sticky; top: 50px; }
        .summary-line { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1em; }
        .total-line { border-top: 2px solid #eee; padding-top: 10px; font-weight: bold; font-size: 1.3em; }
        
        #issue-btn { width: 100%; padding: 15px; font-size: 1.2em; margin-top: 10px; }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.7); display: none; align-items: center; justify-content: center; z-index: 10000; }
    </style>

    <div class="pos-grid">
        <div class="pos-main">
            <!-- 1. Event Selection -->
            <div class="pos-card event-selector-card">
                <label for="event-select"><strong><?php _e( 'Select Event:', 'kueue-events-core' ); ?></strong></label>
                <select id="event-select" style="width: 100%; max-width: 400px; margin-left: 10px;">
                    <option value=""><?php _e( '-- Choose Event --', 'kueue-events-core' ); ?></option>
                    <?php foreach ( $events as $event ) : ?>
                        <option value="<?php echo $event->ID; ?>"><?php echo esc_html( $event->post_title ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Ticket Type Selection -->
            <div class="pos-card" id="ticket-types-section" style="display:none;">
                <h3><?php _e( 'Choose Ticket Type', 'kueue-events-core' ); ?></h3>
                <div class="ticket-type-grid" id="ticket-types-container"></div>
            </div>

            <!-- 3. Booking / Seating (if applicable) -->
            <div class="pos-card" id="addons-section" style="display:none; margin-top:20px;">
                <div id="booking-slots-container"></div>
                <div id="seating-container" style="margin-top:15px;"></div>
            </div>
        </div>

        <div class="pos-sidebar">
            <div class="pos-card summary-card">
                <div class="attendee-form">
                    <h3><?php _e( 'Customer Details', 'kueue-events-core' ); ?></h3>
                    <div class="form-group">
                        <label><?php _e( 'First Name', 'kueue-events-core' ); ?></label>
                        <input type="text" id="cust-first-name" class="regular-text" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label><?php _e( 'Last Name', 'kueue-events-core' ); ?></label>
                        <input type="text" id="cust-last-name" class="regular-text" placeholder="Doe">
                    </div>
                    <div class="form-group">
                        <label><?php _e( 'Email', 'kueue-events-core' ); ?></label>
                        <input type="email" id="cust-email" class="regular-text" placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label><?php _e( 'Phone', 'kueue-events-core' ); ?></label>
                        <input type="text" id="cust-phone" class="regular-text" placeholder="+20...">
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" id="auto-checkin" value="yes"> <?php _e( 'Mark as Checked-in immediately', 'kueue-events-core' ); ?></label>
                    </div>
                </div>

                <div class="order-summary" id="order-summary" style="margin-top:20px;">
                    <div class="summary-line">
                        <span><?php _e( 'Subtotal', 'kueue-events-core' ); ?></span>
                        <span id="summary-subtotal">0.00</span>
                    </div>
                    <div class="summary-line total-line">
                        <span><?php _e( 'Total', 'kueue-events-core' ); ?></span>
                        <span id="summary-total">0.00</span>
                    </div>
                    <button class="button button-primary button-large" id="issue-btn" disabled><?php _e( 'COMPLETE SALE', 'kueue-events-core' ); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loader">
        <div class="spinner is-active" style="float:none;"></div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let selectedTicket = null;
        const restUrl = '<?php echo esc_url_raw( rest_url( "kq/v1/pos" ) ); ?>';
        const nonce = '<?php echo wp_create_nonce( "wp_rest" ); ?>';

        // 1. Fetch Ticket Types when Event changes
        $('#event-select').on('change', function() {
            const eventId = $(this).val();
            if (!eventId) {
                $('#ticket-types-section').hide();
                return;
            }

            $('#loader').css('display', 'flex');
            $.ajax({
                url: '<?php echo esc_url_raw( rest_url( "kq/v1/tickets/types" ) ); ?>?event_id=' + eventId,
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                success: function(response) {
                    $('#loader').hide();
                    renderTicketTypes(response);
                    $('#ticket-types-section').show();
                }
            });
        });

        function renderTicketTypes(types) {
            const container = $('#ticket-types-container');
            container.empty();
            selectedTicket = null;
            updateSummary();

            if (types.length === 0) {
                container.html('<p>No ticket types found for this event.</p>');
                return;
            }

            types.forEach(type => {
                const card = $(`
                    <div class="ticket-card" data-id="${type.id}" data-price="${type.price}">
                        <h3>${type.name}</h3>
                        <div class="ticket-price">${type.price} ${type.currency}</div>
                        <p>${type.description || ''}</p>
                    </div>
                `);

                card.on('click', function() {
                    $('.ticket-card').removeClass('active');
                    $(this).addClass('active');
                    selectedTicket = type;
                    updateSummary();
                });

                container.append(card);
            });
        }

        function updateSummary() {
            if (selectedTicket) {
                $('#summary-subtotal').text(selectedTicket.price);
                $('#summary-total').text(selectedTicket.price);
                $('#issue-btn').prop('disabled', false);
            } else {
                $('#summary-subtotal').text('0.00');
                $('#summary-total').text('0.00');
                $('#issue-btn').prop('disabled', true);
            }
        }

        // 2. Issue Ticket
        $('#issue-btn').on('click', function() {
            if (!selectedTicket) return;

            const data = {
                event_id: $('#event-select').val(),
                ticket_type_id: selectedTicket.id,
                attendee: {
                    first_name: $('#cust-first-name').val(),
                    last_name: $('#cust-last-name').val(),
                    email: $('#cust-email').val(),
                    phone: $('#cust-phone').val()
                },
                auto_checkin: $('#auto-checkin').is(':checked') ? 'yes' : 'no'
            };

            if (!data.attendee.first_name || !data.attendee.email) {
                alert('Please fill at least First Name and Email.');
                return;
            }

            $('#loader').css('display', 'flex');
            $.ajax({
                url: restUrl + '/issue',
                method: 'POST',
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); },
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(response) {
                    $('#loader').hide();
                    if (response.success) {
                        alert('Ticket issued successfully! ID: ' + response.ticket_id);
                        resetForm();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    $('#loader').hide();
                    alert('Request failed: ' + xhr.responseText);
                }
            });
        });

        function resetForm() {
            $('#cust-first-name, #cust-last-name, #cust-email, #cust-phone').val('');
            $('#auto-checkin').prop('checked', false);
            $('.ticket-card').removeClass('active');
            selectedTicket = null;
            updateSummary();
        }
    });
    </script>
</div>
