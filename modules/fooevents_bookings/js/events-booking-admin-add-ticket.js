(function ($) {

    jQuery('#fooevents-add-ticket-container').on('change', '#WooCommerceEventsEvent', function (e) {

        var event = jQuery(this);

        fooevents_bookings_admin_add_ticket_get_booking_options(event);

    });

    jQuery('#fooevents-add-ticket-container').on("change", "#WooCommerceEventsBookingSlotID", function (event) {

        var slot_element = jQuery(this);

        fooevents_admin_bookings_return_slot_dates(slot_element, event);

    });

    jQuery('#fooevents-add-ticket-container').on("change", "#WooCommerceEventsBookingDateID", function (event) {

        var date_element = jQuery(this);
        fooevents_admin_bookings_add_ticket_return_date_stock(date_element, event);

    });

    function fooevents_admin_bookings_add_ticket_return_date_stock(date_element, event) {

        var date_selected = date_element.find(":selected").val();
        var slot_selected = jQuery("#WooCommerceEventsBookingSlotID").find(":selected").val();
        var event_id = jQuery('#WooCommerceEventsEvent').find(":selected").val();

        var data_date_stock = {
            'action': 'fetch_fooevents_bookings_date_stock',
            'slot_selected': slot_selected + '_' + event_id,
            'date_selected': date_selected
        };

        jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data_date_stock, function (response) {

            var returned_stock = JSON.parse(response);
            jQuery("#fooevents-bookings-info").html(returned_stock.stock);

        });

    }

    function fooevents_bookings_admin_add_ticket_get_booking_options(event) {

        var event_id = event.find(":selected").val();

        var data = {
            'action': 'fooevents_fetch_add_ticket_booking_options',
            'event_id': event_id
        };

        jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data, function (response) {

            jQuery('#fooevents-add-ticket-bookings-container tbody').html(response);

        });

    }

    function fooevents_admin_bookings_return_slot_dates(slot_element, event) {

        var slot_id = slot_element.find(":selected").val();
        var event_id = jQuery('#WooCommerceEventsEvent').val();

        var data_slots = {
            'action': 'fetch_fooevents_admin_bookings_dates',
            'slot_id': slot_id,
            'event_id': event_id
        };

        jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data_slots, function (response) {

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
