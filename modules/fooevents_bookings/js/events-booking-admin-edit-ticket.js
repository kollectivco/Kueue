(function ($) {

    if (jQuery("#WooCommerceEventsBookingSlotID").length) {

        jQuery("#WooCommerceEventsBookingSlotID").on("change", function (event) {

            var slot_element = jQuery(this);

            fooevents_admin_bookings_return_slot_dates(slot_element, event);

        });

        jQuery("#WooCommerceEventsBookingDateID").on("change", function (event) {

            var date_element = jQuery(this);
            fooevents_admin_bookings_return_date_stock(date_element, event);

        });

    }

    function fooevents_admin_bookings_return_date_stock(date_element, event) {

        var date_selected = date_element.find(":selected").val();
        var slot_selected = jQuery("#WooCommerceEventsBookingSlotID").find(":selected").val();
        var event_id = jQuery('#fooevents_event_id').val();
        var nonce_val = jQuery('input[name=fooevents_bookings_options_nonce]').val();

        var data_date_stock = {
            'action': 'fetch_fooevents_bookings_date_stock',
            'slot_selected': slot_selected + '_' + event_id,
            'date_selected': date_selected,
            'nonce_val': nonce_val
        };

        jQuery.post(FooEventsBookingsTicketObj.ajaxurl, data_date_stock, function (response) {

            var returned_stock = JSON.parse(response);
            jQuery("#fooevents-bookings-info").html(returned_stock.stock);

        });

    }

    function fooevents_admin_bookings_return_slot_dates(slot_element, event) {

        var slot_id = slot_element.find(":selected").val();
        var ticket_id = jQuery('#fooevents_ticket_raw_id').val();
        var event_id = jQuery('#fooevents_event_id').val();

        var data_slots = {
            'action': 'fetch_fooevents_admin_bookings_dates',
            'slot_id': slot_id,
            'ticket_id': ticket_id,
            'event_id': event_id,
        };

        jQuery.post(FooEventsBookingsTicketObj.ajaxurl, data_slots, function (response) {

            var dates = JSON.parse(response);
            var date_default = jQuery('#WooCommerceEventsBookingDateID').attr('data-placeholder');

            jQuery('#WooCommerceEventsBookingDateID')
                .find('option')
                .remove()
                .end()
                .append('<option value="">' + date_default + '</option>')
                .val('whatever')
                ;

            jQuery.each(dates, function (index, value) {

                jQuery('#WooCommerceEventsBookingDateID').append(jQuery('<option>', {
                    value: index,
                    text: value
                }));

            });

        });

    }
})(jQuery);