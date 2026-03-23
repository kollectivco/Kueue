(function ($) {

    if (jQuery("#fooevents-ticket-details-container").length) {

        jQuery('#post').submit(function (e) {

            e.preventDefault();
            //jQuery('.ajax-loading').show();
            jQuery('.spinner').addClass('is-active');
            jQuery('#publish').addClass('button-primary-disabled');
            var form = this;

            var fields = jQuery('#fooevents-ticket-details-container  :input').serialize();

            var data = {
                'action': 'fooevents_validate_edit_ticket',
                'fields': fields
            };

            jQuery.ajax({

                type: "POST",
                url: FooEventsBookingsEditTicketObj.ajaxurl,
                data: data,
                cache: false

            }).done(function (response) {

                if (jQuery.trim(response) == "") {

                    var input = jQuery("<input>").attr("type", "hidden").attr("name", "fooevents_validation").val("true");
                    jQuery('#fooevents-ticket-details-container').append(input);
                    form.submit();

                } else {

                    var response = JSON.parse(response);
                    alert(response.message);

                }

            }).fail(function () {

                alert('ERROR');

            });

        });

    }

})(jQuery);