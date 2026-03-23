(function ($) {

    jQuery('#fooevents-express-check-in-search').change(function () {

        fooevents_express_check_in_search_check();

    });

    setTimeout(function () {
        jQuery("#fooevents-express-check-in-value").focus();
    }, 0);
    fooevents_express_check_in_search_check();

    var multiday = false;
    var day = 1;
    var nonce_val = $('input[name=fooevents-express-check-in-search-nonce]').val();

    if ($('#fooevents-express-check-in-day').length) {

        multiday = true;
        day = $('#fooevents-express-check-in-day').val();

        jQuery('#fooevents-express-check-in-day').on('change', function () {

            day = $('#fooevents-express-check-in-day').val();

        });

    }

    var input_timer;
    var finish_typing_time = 800;
    var $input = jQuery('#fooevents-express-check-in-value');

    $input.on('keyup', function () {

        clearTimeout(input_timer);
        if (jQuery('#fooevents-express-check-in-search').is(":checked")) {

            input_timer = setTimeout(fooevents_express_check_in_search, finish_typing_time, jQuery('#fooevents-express-check-in-value').val(), multiday, day, nonce_val);

        }

    });

    $input.on('keydown', function () {

        clearTimeout(input_timer);

    });

    jQuery('#fooevents-express-check-in-search-form').on("submit", function () {

        fooevents_express_check_in_search(jQuery('#fooevents-express-check-in-value').val(), multiday, day);

        return false;

    });

    jQuery('#fooevents-express-check-submit').on("click", function () {

        fooevents_express_check_in_search(jQuery('#fooevents-express-check-in-value').val(), multiday, day);

        return false;

    });

    jQuery('.fooevents-express-check-in-checkbox-option').on('change', function () {

        setTimeout(function () {
            jQuery("#fooevents-express-check-in-value").focus();
        }, 0);

    });

    jQuery("#fooevents-express-check-in-output").delegate(".fooevents-express-check-in-control", "click", function () {

        var control_id = jQuery(this).attr("id");
        fooevents_express_check_in_change_status(control_id, multiday, day, nonce_val);
        setTimeout(function () {
            jQuery("#fooevents-express-check-in-value").focus();
        }, 0);

    });


    jQuery("#fooevents-express-check-in-message-wrapper").delegate(".fooevents-express-check-in-undo", "click", function () {

        var undo_id = jQuery(this).attr("id");

        var data = {
            'action': 'undo_check_in',
            'value': undo_id,
            'multiday': multiday,
            'day': day,
            'fooevents-express-check-in-search-nonce': nonce_val
        };

        jQuery.post(ajaxurl, data, function (response) {

            var obj = jQuery.parseJSON(response);

            if (obj.status == 'success') {

                jQuery('<div class="notice notice-success is-dismissible fooevents-express-check-in-message-success fooevents-express-check-in-message-' + obj.status + '"><p>' + obj.status_message + '</p></div>').appendTo("#fooevents-express-check-in-message-wrapper").delay(6000).fadeOut("slow");

            }

        });

        return false;

    });  


    jQuery("#fooevents-express-check-in-output").delegate(".fooevents-express-check-in-show-actions", "click", function () {

        $(this).closest('tr').find('.fooevents-express-check-in-actions-group').toggle();

    });

})(jQuery);

function fooevents_express_check_in_search_check() {

    if (jQuery('#fooevents-express-check-in-search').length) {

        if (jQuery('#fooevents-express-check-in-search').is(":checked")) {

            if (jQuery('#fooevents-express-check-submit').length) {

                jQuery('#fooevents-express-check-submit').prop("disabled", true);
                return true;

            }

        } else {

            jQuery('#fooevents-express-check-submit').prop("disabled", false);
            return false;

        }

    };

}

function fooevents_express_check_in_search(value, multiday, day, nonce_val) {

    jQuery('#fooevents-express-check-in-value').prop("disabled", true);
    jQuery('#fooevents-express-check-in-value').addClass('fooevents-express-check-in-loading');
    jQuery('#fooevents-express-check-in-output').html('');

    var data = {
        'action': 'fooevents_perform_search',
        'value': value,
        'multiday': multiday,
        'fooevents-express-check-in-search-nonce': nonce_val,
        'day': day
    };

    jQuery.post(ajaxurl, data, function (response) {

        jQuery('#fooevents-express-check-in-output').html(response);

    });

    if (jQuery('#fooevents-express-check-in-auto-check-in').is(":checked")) {

        fooevents_express_check_in_change_status_auto_complete(value, multiday, day, nonce_val);

    }

    jQuery("#fooevents-express-check-in-value").focus(function () { jQuery(this).select(); });

    jQuery('#fooevents-express-check-in-value').val('');
    setTimeout(function () {
        jQuery("#fooevents-express-check-in-value").focus();
    }, 0);

    jQuery('#fooevents-express-check-in-value').removeClass('fooevents-express-check-in-loading');
    jQuery('#fooevents-express-check-in-value').prop("disabled", false);

}

function fooevents_express_check_in_change_status(control_id, multiday, day, nonce_val) {

    var data = {
        'action': 'change_ticket_status',
        'value': control_id,
        'multiday': multiday,
        'day': day,
        'fooevents-express-check-in-search-nonce': nonce_val
    };

    jQuery.post(ajaxurl, data, function (response) {

        var enable_sounds = false;
        if (jQuery('#fooevents-express-check-in-enable-sounds').is(":checked")) {

            enable_sounds = true;

        }

        var audio_success = new Audio(FooEventsExpressObj.soundsURL + 'fooevents-success.mp3');
        var audio_error = new Audio(FooEventsExpressObj.soundsURL + 'fooevents-error.mp3');

        var obj = jQuery.parseJSON(response);

        if (obj.status == 'success') {

            if (obj.message == 'Checked In') {

                if (enable_sounds) {

                    audio_success.play();

                }

                jQuery('#' + obj.ID).removeClass('button-primary');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-not-checked-in');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-canceled');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).addClass('fooevents-express-check-in-status-checked-in');

            }

            if (obj.message == 'Not Checked In') {

                jQuery('#fooevents-express-check-in-confirm-' + obj.ticket).addClass('button-primary');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-checked-in');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-canceled');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).addClass('fooevents-express-check-in-status-not-checked-in');

            }

            if (obj.message == 'Canceled') {

                jQuery('#fooevents-express-check-in-confirm-' + obj.ticket).addClass('button-primary');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-checked-in');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).removeClass('fooevents-express-check-in-status-canceled');
                jQuery('#fooevents-express-check-in-status-' + obj.ticket).addClass('fooevents-express-check-in-status-canceled');

            }

            jQuery('#fooevents-express-check-in-status-' + obj.ticket).html(obj.message);
            jQuery('<div class="notice notice-success is-dismissible fooevents-express-check-in-message-success fooevents-express-check-in-message-' + obj.status + '"><p>' + FooEventsExpressObj.successTicketText + ' #' + obj.ticketID + FooEventsExpressObj.hasBeenUpdatedText + '</p></div>').appendTo("#fooevents-express-check-in-message-wrapper").delay(6000).fadeOut("slow");

        } else {

            if (enable_sounds) {

                audio_error.play();

            }

            jQuery('<div class="notice notice-error is-dismissible fooevents-express-check-in-message-error fooevents-express-check-in-message-' + obj.status + '"><p>' + obj.status_message + '</p></div>').appendTo("#fooevents-express-check-in-message-wrapper").delay(6000).fadeOut("slow");

        }

    });

}

function fooevents_express_check_in_change_status_auto_complete(value, multiday, day, nonce_val) {

    var data_auto_complete = {
        'action': 'change_ticket_status_auto_complete',
        'value': value,
        'multiday': multiday,
        'day': day,
        'fooevents-express-check-in-search-nonce': nonce_val
    };

    jQuery.post(ajaxurl, data_auto_complete, function (response) {

        var enable_sounds = false;
        if (jQuery('#fooevents-express-check-in-enable-sounds').is(":checked")) {

            enable_sounds = true;

        }

        var audio_success = new Audio(FooEventsExpressObj.soundsURL + 'fooevents-success.mp3');
        var audio_error = new Audio(FooEventsExpressObj.soundsURL + 'fooevents-error.mp3');

        var obj = jQuery.parseJSON(response);

        if (obj.status == 'success') {

            if (enable_sounds) {

                audio_success.play();

            }

            jQuery('<div class="notice notice-success is-dismissible fooevents-express-check-in-message-error fooevents-express-check-in-message-' + obj.status + '"><p>' + obj.status_message + '</p></div>').appendTo("#fooevents-express-check-in-message-wrapper").delay(6000).fadeOut("slow");
            jQuery('#fooevents-express-check-in-confirm-' + obj.ticket).removeClass('button-primary');
            jQuery('#fooevents-express-check-in-status-' + obj.ticket).html(obj.message);

        } else {

            if (enable_sounds) {

                audio_error.play();

            }

            jQuery('<div class="notice notice-error is-dismissible fooevents-express-check-in-message-error fooevents-express-check-in-message-' + obj.status + '"><p>' + obj.status_message + '</p></div>').appendTo("#fooevents-express-check-in-message-wrapper").delay(6000).fadeOut("slow");

        }

    });


}