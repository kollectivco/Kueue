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

        // 1. Fetch Ticket Types and Event Settings when Event changes
        $('#event-select').on('change', function() {
            const eventId = $(this).val();
            if (!eventId) {
                $('#ticket-types-section, #addons-section').hide();
                return;
            }

            $('#loader').css('display', 'flex');
            
            // Fetch Ticket Types
            const fetchTickets = $.ajax({
                url: '<?php echo esc_url_raw( rest_url( "kq/v1/tickets/types" ) ); ?>?event_id=' + eventId,
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); }
            });

            // Fetch Event Settings (Addons)
            const fetchSettings = $.ajax({
                url: '<?php echo esc_url_raw( rest_url( "kq/v1/pos/event-settings" ) ); ?>?event_id=' + eventId,
                beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', nonce); }
            });

            $.when(fetchTickets, fetchSettings).done(function(ticketsRes, settingsRes) {
                $('#loader').hide();
                
                renderTicketTypes(ticketsRes[0]);
                $('#ticket-types-section').show();

                renderAddons(settingsRes[0], eventId);
            });
        });

        function renderAddons(settings, eventId) {
            const $addons = $('#addons-section');
            const $bookingTarget = $('#booking-slots-container');
            const $seatingTarget = $('#seating-container');

            $bookingTarget.empty();
            $seatingTarget.empty();

            if (!settings.enable_bookings && !settings.enable_seating) {
                $addons.hide();
                return;
            }

            $addons.show();

            if (settings.enable_bookings) {
                let html = '<h4><?php _e( 'Booking Selection', 'kueue-events-core' ); ?></h4>';
                html += '<div style="display:flex; gap:10px;">';
                html += '<select id="pos-booking-date" style="flex:1;"><option value="">-- Select Date --</option>';
                settings.booking_dates.forEach(d => {
                    html += `<option value="${d.id}">${d.event_date}</option>`;
                });
                html += '</select>';
                html += '<select id="pos-booking-slot" style="flex:1;" disabled><option value="">-- Select Date First --</option></select>';
                html += '</div>';
                $bookingTarget.html(html);

                $('#pos-booking-date').on('change', function() {
                    const dateId = $(this).val();
                    if (!dateId) {
                        $('#pos-booking-slot').prop('disabled', true).html('<option value="">-- Select Date First --</option>');
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        type: 'POST',
                        data: { action: 'kq_get_slots', date_id: dateId, nonce: '<?php echo wp_create_nonce("kq-nonce"); ?>' },
                        success: function(res) {
                            if (res.success) {
                                let sHtml = '<option value="">-- Select Slot --</option>';
                                res.data.forEach(s => {
                                    sHtml += `<option value="${s.id}">${s.start_time} - ${s.end_time} (${s.capacity - s.sold_count} open)</option>`;
                                });
                                $('#pos-booking-slot').html(sHtml).prop('disabled', false);
                            }
                        }
                    });
                });
            }

            if (settings.enable_seating && settings.seating_map) {
                let html = '<h4><?php _e( 'Assigned Seating', 'kueue-events-core' ); ?></h4>';
                html += '<div id="pos-seating-grid" style="background:#f4f4f4; padding:15px; border-radius:8px; border:1px solid #ddd;">';
                html += '<p style="margin:0 0 10px; font-size:12px; color:#666;">Click "Refresh Map" to see current availability.</p>';
                html += '<button type="button" class="button" id="pos-refresh-seating">Refresh Seating Map</button>';
                html += '<div id="pos-map-viewer" style="margin-top:15px; display:grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap:5px;"></div>';
                html += '</div>';
                html += '<input type="hidden" id="pos-selected-seat-id" value="">';
                html += '<div id="pos-seat-indicator" style="margin-top:10px; font-weight:bold; color:#0073aa;"></div>';
                $seatingTarget.html(html);

                $('#pos-refresh-seating').on('click', function() {
                     const $viewer = $('#pos-map-viewer');
                     $viewer.html('<span class="spinner is-active"></span>');
                     
                     $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        type: 'POST',
                        data: { action: 'kq_get_seating_data', event_id: eventId, nonce: '<?php echo wp_create_nonce("kq-nonce"); ?>' },
                        success: function(res) {
                            if (res.success) {
                                $viewer.empty();
                                res.data.sections.forEach(sec => {
                                    sec.rows.forEach(row => {
                                        row.seats.forEach(seat => {
                                            const statusClass = seat.status === 'available' ? 'seat-avail' : 'seat-sold';
                                            const $seatEl = $(`<div title="${seat.seat_label}" class="seat-node ${statusClass}" data-id="${seat.id}" style="width:30px; height:30px; border-radius:4px; border:1px solid #ccc; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:10px; background:${seat.status === 'available' ? '#fff' : '#ddd'};">${seat.seat_label}</div>`);
                                            
                                            if (seat.status === 'available') {
                                                $seatEl.on('click', function() {
                                                    $('.seat-node').css('border-color', '#ccc').css('background', '#fff');
                                                    $('.seat-sold').css('background', '#ddd');
                                                    $(this).css('border-color', '#0073aa').css('background', '#e0f0ff');
                                                    $('#pos-selected-seat-id').val(seat.id);
                                                    $('#pos-seat-indicator').text('Selected Seat: ' + seat.seat_label);
                                                });
                                            } else {
                                                $seatEl.css('cursor', 'not-allowed').css('opacity', '0.5');
                                            }
                                            $viewer.append($seatEl);
                                        });
                                    });
                                });
                            }
                        }
                     });
                });
            }
        }

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
                booking_slot_id: $('#pos-booking-slot').val(),
                seat_id: $('#pos-selected-seat-id').val(),
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
