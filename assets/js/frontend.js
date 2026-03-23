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
            
            // Collect attendee data if form exists
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
                    attendees: attendeeData
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

    });

})(jQuery);
