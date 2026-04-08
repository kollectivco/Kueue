/**
 * Kueue Events Frontend Interactions
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Handle Add to Cart
        $(document).on('click', '.kq-add-to-cart-btn', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const ticketTypeId = $btn.data('ticket-id');
            const qty = $('#kq-qty-' + ticketTypeId).val() || 1;
            
            // Collect booking/seat data
            const bookingSlotId = $('#kq-booking-slot').val();
            const seatId = $('#kq-selected-seat-id').val();

            // Collect attendee data
            let attendeeData = [];
            $('.kq-attendee-row-' + ticketTypeId).each(function() {
                attendeeData.push({
                    first_name: $(this).find('.kq-att-fname').val(),
                    last_name: $(this).find('.kq-att-lname').val(),
                    email: $(this).find('.kq-att-email').val()
                });
            });

            $btn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: kq_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'kq_add_to_cart',
                    nonce: kq_ajax.nonce,
                    ticket_type_id: ticketTypeId,
                    qty: qty,
                    attendees: attendeeData,
                    booking_slot_id: bookingSlotId,
                    seat_id: seatId
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect_url;
                    } else {
                        alert(response.data.message || 'Error adding to cart');
                        $btn.prop('disabled', false).text('Add to Cart');
                    }
                },
                error: function() {
                    alert('Server error. Please try again.');
                    $btn.prop('disabled', false).text('Add to Cart');
                }
            });
        });

        // Booking Date -> Slot change
        $(document).on('change', '#kq-booking-date', function() {
             const dateId = $(this).val();
             const $slotSelect = $('#kq-booking-slot');
             
             if (!dateId) {
                 $slotSelect.prop('disabled', true).html('<option value="">-- Select Date First --</option>');
                 return;
             }

             $slotSelect.prop('disabled', true).html('<option value="">Loading slots...</option>');

             $.ajax({
                 url: kq_ajax.ajax_url,
                 type: 'POST',
                 data: {
                     action: 'kq_get_slots',
                     nonce: kq_ajax.nonce,
                     date_id: dateId
                 },
                 success: function(response) {
                     if (response.success) {
                         let html = '<option value="">-- Select Time Slot --</option>';
                         response.data.forEach(slot => {
                             html += `<option value="${slot.id}">${slot.start_time} - ${slot.end_time} (${slot.capacity - slot.sold_count} left)</option>`;
                         });
                         $slotSelect.html(html).prop('disabled', false);
                     } else {
                         $slotSelect.html('<option value="">Error loading slots</option>');
                     }
                 }
             });
        });

        // Seating Map Toggle (Simplified)
        $(document).on('click', '#kq-open-seating-map', function() {
            // For now, prompt for a seat ID as a simulation of map selection
            // In real app, this would open a modal with the SVG/Grid map
            const seatId = prompt('Enter Seat ID (DEBUG MODE):');
            if (seatId) {
                $('#kq-selected-seat-id').val(seatId);
                $('#kq-selected-seat-display').text('Selected Seat: ' + seatId).show();
            }
        });

        // Dynamic attendee fields based on quantity
        $(document).on('change', '.kq-qty-selector', function() {
            const qty = parseInt($(this).val());
            const ticketId = $(this).data('ticket-id');
            const $container = $('#kq-attendee-fields-' + ticketId);
            
            // Logic to add/remove rows
            let currentRows = $container.find('.kq-attendee-row').length;
            
            if (qty > currentRows) {
                for (let i = currentRows + 1; i <= qty; i++) {
                    $container.append(`
                        <div class="kq-attendee-row kq-attendee-row-${ticketId}" style="margin-top:10px; padding:10px; border:1px solid #eee; border-radius:8px;">
                            <small>Attendee ${i}</small>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:5px;">
                                <input type="text" class="kq-input kq-att-fname" placeholder="First Name" required>
                                <input type="text" class="kq-input kq-att-lname" placeholder="Last Name" required>
                            </div>
                            <input type="email" class="kq-input kq-att-email" placeholder="Email Address" required>
                        </div>
                    `);
                }
            } else if (qty < currentRows) {
                $container.find('.kq-attendee-row').slice(qty).remove();
            }
        });

        // Handle Payout Request
        $(document).on('click', '#kq-request-payout', function(e) {
            e.preventDefault();
            const $btn = $(this);

            if (!confirm('Are you sure you want to request a withdrawal of your current earnings?')) return;

            $btn.prop('disabled', true).text('Processing...');

            $.post(kq_ajax.ajax_url, {
                action: 'kq_request_payout',
                nonce: kq_ajax.nonce
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message || 'Error submitting request');
                    $btn.prop('disabled', false).text('Request Payout');
                }
            });
        });

        // Handle Resend Ticket
        $(document).on('click', '.kq-resend-ticket', function() {
            const id = $(this).data('id');
            const $btn = $(this);
            
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.post(kq_ajax.ajax_url, {
                action: 'kq_resend_ticket',
                id: id,
                nonce: kq_ajax.nonce
            }, function(response) {
                alert(response.data.message);
                $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i>');
            });
        });

        // Handle Cancel Ticket
        $(document).on('click', '.kq-cancel-ticket', function() {
            const id = $(this).data('id');
            const $btn = $(this);
            
            if (!confirm('Are you sure you want to cancel this ticket? This will release the seat/slot.')) return;
            
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.post(kq_ajax.ajax_url, {
                action: 'kq_cancel_ticket',
                id: id,
                nonce: kq_ajax.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).html('<i class="fa fa-times-circle"></i>');
                }
            });
        });

    });

})(jQuery);
