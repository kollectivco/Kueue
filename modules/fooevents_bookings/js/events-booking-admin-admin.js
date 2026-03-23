(function ($) {

    fooevents_bookings_admin_datepicker();

    var event_id = jQuery('#fooevents-bookings-product').val();
    var slot_id = jQuery('#fooevents-bookings-slot').val();

    if (event_id) {

        fooevents_bookings_get_product_dates(event_id, slot_id);

    }

    jQuery('#fooevents-bookings-listing').on('change', '#fooevents-bookings-product', function (event) {

        var event_id = jQuery('#fooevents-bookings-product').val();
        var slot_id = jQuery('#fooevents-bookings-slot').val();
        jQuery("#fooevents-bookings-admin-date").val('');

        fooevents_bookings_get_product_slots(event_id);
        fooevents_bookings_get_product_dates(event_id, slot_id);

        return false;

    });

    jQuery('#fooevents-bookings-listing').on('change', '#fooevents-bookings-slot', function (event) {


        var event_id = jQuery('#fooevents-bookings-product').val();
        var slot_id = jQuery('#fooevents-bookings-slot').val();
        jQuery("#fooevents-bookings-admin-date").val('');

        fooevents_bookings_get_product_dates(event_id, slot_id);

        return false;

    });

    function fooevents_bookings_get_product_slots(event_id) {

        var data = {
            'action': 'fooevents_bookings_admin_get_slots',
            'event_id': event_id,
        };

        jQuery.post(FooEventsBookingsAdminObj.ajaxurl, data, function (response) {

            var slots = JSON.parse(response);

            jQuery('#fooevents-bookings-slot').find('option:not(:first)').remove();

            jQuery.each(slots, function (index, value) {

                jQuery('#fooevents-bookings-slot').append(jQuery('<option>', {
                    value: index,
                    text: value
                }));

            });

        });


    }

    function fooevents_bookings_get_product_dates(event_id, slot_id) {

        if (event_id) {

            var data = {
                'action': 'fooevents_bookings_admin_get_dates',
                'event_id': event_id,
                'slot_id': slot_id
            };

            jQuery("#fooevents-bookings-slot").prop('disabled', true);
            jQuery("#fooevents-bookings-admin-date").prop('disabled', true);
            jQuery("#fooevents-bookings-admin-button").prop('disabled', true);
            jQuery.post(FooEventsBookingsAdminObj.ajaxurl, data, function (response) {

                var dates = JSON.parse(response);

                fooevents_bookings_admin_datepicker_selection(dates);

            }).done(function () {

                jQuery("#fooevents-bookings-slot").prop('disabled', false);
                jQuery("#fooevents-bookings-admin-date").prop('disabled', false);
                jQuery("#fooevents-bookings-admin-button").prop('disabled', false);

            });

        } else {

            fooevents_bookings_admin_datepicker();

        }

    }

    function fooevents_bookings_admin_datepicker() {

        if ((typeof FooEventsBookingsAdminObj === "object") && (FooEventsBookingsAdminObj !== null)) {

            jQuery("#fooevents-bookings-admin-date").datepicker("destroy");

            jQuery('#fooevents-bookings-admin-date').datepicker({
                showButtonPanel: true,
                closeText: FooEventsBookingsAdminObj.closeText,
                currentText: FooEventsBookingsAdminObj.currentText,
                monthNames: FooEventsBookingsAdminObj.monthNames,
                monthNamesShort: FooEventsBookingsAdminObj.monthNamesShort,
                dayNames: FooEventsBookingsAdminObj.dayNames,
                dayNamesShort: FooEventsBookingsAdminObj.dayNamesShort,
                dayNamesMin: FooEventsBookingsAdminObj.dayNamesMin,
                dateFormat: 'yy-mm-dd',
                firstDay: FooEventsBookingsAdminObj.firstDay,
                isRTL: FooEventsBookingsAdminObj.isRTL
            });

        } else {

            jQuery('#fooevents-bookings-admin-date').datepicker();

        }

    }

    function fooevents_bookings_admin_datepicker_selection(dates) {

        if ((typeof FooEventsBookingsAdminObj === "object") && (FooEventsBookingsAdminObj !== null)) {

            jQuery("#fooevents-bookings-admin-date").datepicker("destroy");

            jQuery('#fooevents-bookings-admin-date').datepicker({
                showButtonPanel: true,
                closeText: FooEventsBookingsAdminObj.closeText,
                currentText: FooEventsBookingsAdminObj.currentText,
                monthNames: FooEventsBookingsAdminObj.monthNames,
                monthNamesShort: FooEventsBookingsAdminObj.monthNamesShort,
                dayNames: FooEventsBookingsAdminObj.dayNames,
                dayNamesShort: FooEventsBookingsAdminObj.dayNamesShort,
                dayNamesMin: FooEventsBookingsAdminObj.dayNamesMin,
                dateFormat: 'yy-mm-dd',
                firstDay: FooEventsBookingsAdminObj.firstDay,
                isRTL: FooEventsBookingsAdminObj.isRTL,
                beforeShowDay: function (d) {

                    if (dates instanceof Array) {

                        var dmy = (d.getMonth() + 1)
                        if (d.getMonth() < 9)
                            dmy = "0" + dmy;
                        dmy += "-";

                        if (d.getDate() < 10) dmy += "0";
                        dmy += d.getDate() + "-" + d.getFullYear();

                        if ($.inArray(dmy, dates) != -1) {
                            return [true, "", "Booking"];
                        } else {
                            return [false, "", "No Bookings"];
                        }

                    }

                }
            });

        }

    }


})(jQuery);   