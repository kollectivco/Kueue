(function ($) {

    nonce_val = jQuery('input[name=fooevents_add_ticket_page_nonce]').val();

    jQuery('#fooevents-add-ticket-container').on('change', '#WooCommerceEventsEvent', function (e) {

        var event = jQuery(this);

        fooevents_get_event_variations(event);
        fooevents_get_event_details(event);

    });

    jQuery('#WooCommerceEventsClientID').on("change", function () {

        var selector = jQuery(this);
        fooevents_update_purchaser(selector);

    });

    jQuery('#WooCommerceEventsPurchaserFirstName, #WooCommerceEventsPurchaserLastName').on("change", function () {

        var first_name = jQuery('#WooCommerceEventsPurchaserFirstName').val();
        var last_name = jQuery('#WooCommerceEventsPurchaserLastName').val();

        jQuery('#WooCommerceEventsPurchaserDisplayName').val(first_name + ' ' + last_name);

    });

    jQuery('.fooevents-search-list-add-ticket').select2({
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    security: nonce_val,
                    action: 'fooevents_get_users'
                };
            },
            processResults: function (data) {
                var options = [];
                if (data) {
                    $.each(data, function (index, text) {
                        options.push({ id: index, text: text });
                    });
                }
                return {
                    results: options
                };
            },
            cache: true
        },
        minimumInputLength: 3 // the minimum of symbols to input before perform a search

    });


    jQuery('#post').submit(function (e) {

        e.preventDefault();
        var form = this;

        var fields = jQuery('#fooevents-add-ticket-container  :input').serialize();

        var data = {
            'action': 'fooevents_validate_add_ticket',
            'fields': fields
        };

        jQuery.ajax({

            type: "POST",
            url: FooEventsBookingsAddTicketObj.ajaxurl,
            data: data,
            cache: false

        }).done(function (response) {

            if (jQuery.trim(response) == "") {

                var input = jQuery("<input>").attr("type", "hidden").attr("name", "fooevents_validation").val("true");
                jQuery('#fooevents-add-ticket-container').append(input);
                form.submit();

            } else {

                var response = JSON.parse(response);
                alert(response.message);

            }

        }).fail(function () {

            alert('ERROR');

        });

    });

    function fooevents_bookings_add_ticket_validate() {

        var slot_id = jQuery('#WooCommerceEventsBookingSlotID').val();
        var date_id = jQuery('#WooCommerceEventsBookingDateID').val();
        var event_id = jQuery('#WooCommerceEventsEvent').find(":selected").val();

        if (!slot_id) {

            alert('Booking slot is required.');

            return false;

        }

        if (!date_id) {

            alert('Booking Date is required.');

            return false;

        }

        var data = {
            'action': 'fooevents_admin_add_ticket_bookings_validate',
            'slot_id': slot_id,
            'date_id': date_id,
            'event_id': event_id
        };

        jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data, function (response) {

            var responseObj = JSON.parse(response);

            if (!responseObj.success) {

                alert(responseObj.message);
                return false;

            }

        });

        return true;
    }

    function fooevents_get_event_variations(event) {

        var event_id = event.find(":selected").val();

        var data = {
            'action': 'get_event_variations',
            'event_id': event_id
        };

        jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data, function (response) {

            var variations = JSON.parse(response);

            if (variations.status) {
                var variation_option = '<div class="ticket-details-row">';

                variation_option += '<label>Variation:</label>';
                variation_option += '<select name="WooCommerceEventsSelectedVariation" id="WooCommerceEventsSelectedVariation">';

                jQuery.each(variations.variations, function (index, value) {

                    variation_option += '<option value="' + index + '">' + value + '</option>';

                });
                variation_option += '</select>';
                variation_option += '</div>';
                jQuery('#fooevents-event-variation-options').html(variation_option);

            } else {

                jQuery('#fooevents-event-variation-options').html('');

            }

        });

    }

    function fooevents_get_event_details(event) {

        var event_id = event.find(":selected").val();

        if (event_id) {

            jQuery('.fooevents-event-details tbody').html('<h3>' + FooEventsBookingsAddTicketObj.eventOverview + '</h3><img src="' + FooEventsBookingsAddTicketObj.adminURL + 'images/loading.gif" class="fooevents-ajax-spinner" />');

            var data = {
                'action': 'get_event_details',
                'event_id': event_id
            };

            jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data, function (response) {

                if (response) {

                    jQuery('.fooevents-event-details tbody').html(response);

                } else {

                    jQuery('.fooevents-event-details tbody').html('');

                }

            });

        } else {

            jQuery('.fooevents-event-details tbody').html('<tr valign="top"><td><h3>' + FooEventsBookingsAddTicketObj.eventOverview + '</h3><i>' + FooEventsBookingsAddTicketObj.selectEvent + '</i></td></tr>');

        }

    }

    function fooevents_update_purchaser(selector) {

        var userID = selector.val();

        jQuery('#WooCommerceEventsPurchaserFirstName').val('');
        jQuery("#WooCommerceEventsPurchaserFirstName").removeAttr("readonly");
        jQuery('#WooCommerceEventsPurchaserLastName').val('');
        jQuery("#WooCommerceEventsPurchaserLastName").removeAttr("readonly");
        jQuery('#WooCommerceEventsPurchaserDisplayName').val('');
        jQuery("#WooCommerceEventsPurchaserDisplayName").removeAttr("readonly");
        jQuery('#WooCommerceEventsPurchaserEmail').val('');
        jQuery("#WooCommerceEventsPurchaserEmail").removeAttr("readonly");
        jQuery('#WooCommerceEventsPurchaserUserName').val('');
        jQuery("#WooCommerceEventsPurchaserUserName").removeAttr("readonly");

        if (userID) {

            var data = {
                'action': 'fetch_wordpress_user',
                'userID': userID
            };

            jQuery.post(FooEventsBookingsAddTicketObj.ajaxurl, data, function (response) {

                var user = JSON.parse(response);
                console.log(user);
                if (user.ID) {

                    jQuery('#WooCommerceEventsPurchaserUserName').val(user.user_login);
                    jQuery("#WooCommerceEventsPurchaserUserName").prop('readonly', true);
                    jQuery('#WooCommerceEventsPurchaserFirstName').val(user.first_name);
                    jQuery("#WooCommerceEventsPurchaserFirstName").prop('readonly', true);
                    jQuery('#WooCommerceEventsPurchaserLastName').val(user.last_name);
                    jQuery("#WooCommerceEventsPurchaserLastName").prop('readonly', true);
                    jQuery('#WooCommerceEventsPurchaserDisplayName').val(user.display_name);
                    jQuery("#WooCommerceEventsPurchaserDisplayName").prop('readonly', true);
                    jQuery('#WooCommerceEventsPurchaserEmail').val(user.user_email);
                    jQuery("#WooCommerceEventsPurchaserEmail").prop('readonly', true);

                }

            });

        }

    }

})(jQuery);