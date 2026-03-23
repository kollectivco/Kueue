(function ($) {


    toggleForm();

    // Handle changes in the dropdown
    jQuery('#WooCommerceEventsProductIsEvent').change(function() {
            toggleForm();
    });

    function toggleForm() {
        if (jQuery('#WooCommerceEventsProductIsEvent').prop('checked') === false) {
            jQuery('#WooCommerceEventsForm').hide();
            jQuery('.custom_tab_fooevents_tickets').hide();
            jQuery('.custom_tab_booking_options').hide();
            jQuery('.custom_tab_fooevents_terminology').hide();
            jQuery('.custom_tab_custom_attendee_options').hide();
            jQuery('.custom_tab_seating_options').hide();
            jQuery('.custom_tab_fooevents_exports').hide();
            jQuery('.custom_tab_fooevents_printing').hide();
            jQuery('.custom_tab_fooevents_expiration').hide();
            jQuery('.custom_tab_fooevents_integration').hide();

        } else {
            jQuery('#WooCommerceEventsForm').show();
            jQuery('.custom_tab_fooevents_tickets').show();
            jQuery('.custom_tab_fooevents_terminology').show();
            jQuery('.custom_tab_custom_attendee_options').show();
            jQuery('.custom_tab_fooevents_exports').show();
            jQuery('.custom_tab_fooevents_printing').show();
            jQuery('.custom_tab_fooevents_expiration').show();
            jQuery('.custom_tab_fooevents_integration').show();       
        }
    }

    var fileInput = '';

    jQuery('.fooevents-tooltip').tooltip({
        tooltipClass: "fooevents-tooltip-box",
    });

    jQuery('.wrap').on('click', '.upload_image_button_woocommerce_events', function (e) {
        e.preventDefault();

        var button = jQuery(this);
        var uploadInput = jQuery(this).parent().prev('input.uploadfield');

        wp.media.editor.send.attachment = function (props, attachment) {
            jQuery(uploadInput).attr("value", attachment.url);

        };

        wp.media.editor.open(button);
        return false;
    });

    jQuery('.upload_reset').click(function () {

        //jQuery(this).parent().find('input.uploadfield').val('');
        jQuery(this).closest('.options_group').find('.uploadfield').val('');
        return false;

    });

    jQuery('#fooevents-settings-page .upload_reset').click(function () {

        jQuery(this).closest('td').find('.uploadfield').val('');
        return false;

    });

    jQuery("#WooCommerceEventsReport .form-submit a").click(function () {
        var date = this.name;
        jQuery('#WooCommerceEventsDateFrom').val(date);
    });

    jQuery('.woocommerce-events-color-field').wpColorPicker();
    if (jQuery("#WooCommerceEventsProductIsEvent").length) {

        if ((typeof FooEventsObj === "object") && (FooEventsObj !== null)) {

            jQuery('#WooCommerceEventsDate').datepicker({

                showButtonPanel: true,
                closeText: FooEventsObj.closeText,
                currentText: FooEventsObj.currentText,
                monthNames: FooEventsObj.monthNames,
                monthNamesShort: FooEventsObj.monthNamesShort,
                dayNames: FooEventsObj.dayNames,
                dayNamesShort: FooEventsObj.dayNamesShort,
                dayNamesMin: FooEventsObj.dayNamesMin,
                dateFormat: FooEventsObj.dateFormat,
                firstDay: FooEventsObj.firstDay,
                isRTL: FooEventsObj.isRTL,

            });

            jQuery('#WooCommerceEventsExpire, #WooCommerceEventsTicketsExpireSelect').datetimepicker({

                showButtonPanel: true,
                closeText: FooEventsObj.closeText,
                currentText: FooEventsObj.currentText,
                monthNames: FooEventsObj.monthNames,
                monthNamesShort: FooEventsObj.monthNamesShort,
                dayNames: FooEventsObj.dayNames,
                dayNamesShort: FooEventsObj.dayNamesShort,
                dayNamesMin: FooEventsObj.dayNamesMin,
                dateFormat: FooEventsObj.dateFormat,
                firstDay: FooEventsObj.firstDay,
                isRTL: FooEventsObj.isRTL,

            });

        } else {

            jQuery('#WooCommerceEventsDate').datepicker();
            jQuery('#WooCommerceEventsExpire, #WooCommerceEventsTicketsExpireSelect').datetimepicker();

        }

        if ((typeof FooEventsObj === "object") && (FooEventsObj !== null)) {

            jQuery('#WooCommerceEventsEndDate').datepicker({

                showButtonPanel: true,
                closeText: FooEventsObj.closeText,
                currentText: FooEventsObj.currentText,
                monthNames: FooEventsObj.monthNames,
                monthNamesShort: FooEventsObj.monthNamesShort,
                dayNames: FooEventsObj.dayNames,
                dayNamesShort: FooEventsObj.dayNamesShort,
                dayNamesMin: FooEventsObj.dayNamesMin,
                dateFormat: FooEventsObj.dateFormat,
                firstDay: FooEventsObj.firstDay,
                isRTL: FooEventsObj.isRTL,

            });

        } else {

            jQuery('#WooCommerceEventsEndDate').datepicker();

        }

        jQuery('.wrap').on('change', '#WooCommerceEventsExportUnpaidTicketsExport', function (e) {
            showUpdateExportMessage();
        });

        jQuery('.wrap').on('change', '#WooCommerceEventsExportBillingDetailsExport', function (e) {
            showUpdateExportMessage();
        });

    }

    if (jQuery(".fooevents-csv-error").length) {

        jQuery('#fooevents-csv-confirmation-button').prop('disabled', true);

    }

    function showUpdateExportMessage() {

        jQuery('#WooCommerceEventsExportMessage').html('Update product for export options to take affect.');

    }

    var ticketExpirationType = jQuery('input[name=WooCommerceEventsTicketExpirationType]:checked').val();

    if (ticketExpirationType == 'select') {

        jQuery('#WooCommerceEventsTicketsExpireValue, #WooCommerceEventsTicketsExpireUnit').prop('disabled', true);

    }

    if (ticketExpirationType == 'time') {

        jQuery('#WooCommerceEventsTicketsExpireSelect').prop('disabled', true);

    }

    jQuery('input[name=WooCommerceEventsTicketExpirationType]').change(function () {

        var ticketExpirationType = this.value;

        if (ticketExpirationType == 'select') {

            jQuery('#WooCommerceEventsTicketsExpireValue, #WooCommerceEventsTicketsExpireUnit').prop('disabled', true);
            jQuery('#WooCommerceEventsTicketsExpireSelect').prop('disabled', false);

        }

        if (ticketExpirationType == 'time') {

            jQuery('#WooCommerceEventsTicketsExpireSelect').prop('disabled', true);
            jQuery('#WooCommerceEventsTicketsExpireValue, #WooCommerceEventsTicketsExpireUnit').prop('disabled', false);

        }

    });

})(jQuery);
(function ($) {

    jQuery('.woocommerce-events-color-field').wpColorPicker();

})(jQuery);
(function ($) {

    var postID = getParameter('post');

    if(! postID) {

        var postID = getParameter('id');

    }

    jQuery('#WooCommerceEventsResendTicket').on("click", function () {

        jQuery('#WooCommerceEventsResendTicketMessage').html("<div class='notice notice-info'>Sending...</div>");
        var WooCommerceEventsResendTicketEmail = jQuery('#WooCommerceEventsResendTicketEmail').val();
        if (!WooCommerceEventsResendTicketEmail) {

            jQuery('#WooCommerceEventsResendTicketMessage').html("<div class='notice notice-error'>Email address required.</div>");

        } else {

            var data = {
                'action': 'resend_ticket',
                'WooCommerceEventsResendTicketEmail': WooCommerceEventsResendTicketEmail,
                'postID': postID
            };

            jQuery.post(ajaxurl, data, function (response) {

                var email = JSON.parse(response);
                jQuery('#WooCommerceEventsResendTicketMessage').html("<div class='notice notice-success'>" + email.message + "</div>");

            });

        }

        return false;
    });

    jQuery('#WooCommerceEventsResendOrderTicket').on("click", function () {

        jQuery('#WooCommerceEventsResendOrderTicketMessage').html("<div class='notice notice-info'>Sending...</div>");
        var WooCommerceEventsResendOrderTicketEmail = jQuery('#WooCommerceEventsResendOrderTicketEmail').val();
        if (!WooCommerceEventsResendOrderTicketEmail) {

            jQuery('#WooCommerceEventsResendOrderTicketMessage').html("<div class='notice notice-error'>Email address required.</div>");

        } else {

            var data = {
                'action': 'resend_order_ticket',
                'WooCommerceEventsResendOrderTicketEmail': WooCommerceEventsResendOrderTicketEmail,
                'postID': postID
            };

            jQuery.post(ajaxurl, data, function (response) {

                var email = JSON.parse(response);
                jQuery('#WooCommerceEventsResendOrderTicketMessage').html("<div class='notice notice-success'>" + email.message + "</div>");

            });

        }

        return false;
    });

    function getParameter(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        else {
            return results[1] || 0;
        }
    }

})(jQuery);
(function ($) {

    if (jQuery('input[name="globalWooCommerceEventsAppEvents"]').length > 0) {
        jQuery('input[name="globalWooCommerceEventsAppEvents"]').change(function () {
            if (jQuery(this).val() === 'id') {
                jQuery('select#globalWooCommerceEventsAppEventIDs').removeAttr('disabled');
            } else {
                jQuery('select#globalWooCommerceEventsAppEventIDs').attr('disabled', 'disabled');
            }
        });
    }

})(jQuery);

(function ($) {

    if (jQuery('input[name="globalWooCommerceEventsAppEvents"]').length > 0) {
        jQuery('input[name="globalWooCommerceEventsAppEvents"]').change(function () {
            if (jQuery(this).val() === 'all') {
                jQuery('#globalWooCommerceEventsAppShowAllForAdmin').prop('checked', true).attr('disabled', 'disabled');
            } else {
                jQuery('#globalWooCommerceEventsAppShowAllForAdmin').prop('checked', false).removeAttr('disabled');
            }
        });
    }

})(jQuery);

(function ($) {

    if (jQuery('#WooCommerceEventsHour').length && jQuery('#WooCommerceEventsHourEnd').length) {
        jQuery('#WooCommerceEventsHour').change(function () {
            if (parseInt(jQuery(this).val()) > 12 || parseInt(jQuery('#WooCommerceEventsHourEnd').val()) > 12) {
                jQuery('#WooCommerceEventsPeriod').val('').prop('disabled', true);
                jQuery('#WooCommerceEventsEndPeriod').val('').prop('disabled', true);
            } else {
                jQuery('#WooCommerceEventsPeriod').prop('disabled', false);
                jQuery('#WooCommerceEventsEndPeriod').prop('disabled', false);
            }
        });

        jQuery('select#WooCommerceEventsHourEnd').change(function () {
            if (parseInt(jQuery(this).val()) > 12 || parseInt(jQuery('#WooCommerceEventsHour').val()) > 12) {
                jQuery('#WooCommerceEventsPeriod').val('').prop('disabled', true);
                jQuery('#WooCommerceEventsEndPeriod').val('').prop('disabled', true);
            } else {
                jQuery('#WooCommerceEventsPeriod').prop('disabled', false);
                jQuery('#WooCommerceEventsEndPeriod').prop('disabled', false);
            }
        });
    }

})(jQuery);

(function ($) {

    if (jQuery('#WooCommerceEventsStatusMeta').length && jQuery('#WooCommerceEventsMultidayStatusMeta').length) {
        jQuery('#WooCommerceEventsStatusMeta').change(function () {
            var status = jQuery(this).val();

            jQuery('#WooCommerceEventsMultidayStatusMeta select').val(status);
        });

        jQuery('#WooCommerceEventsMultidayStatusMeta select').change(function () {
            var thisStatus = jQuery(this).val();
            var changeMain = true;

            jQuery('#WooCommerceEventsMultidayStatusMeta select').each(function () {
                if (jQuery(this).val() != thisStatus) {
                    changeMain = false;
                }
            });

            if (changeMain) {
                jQuery('#WooCommerceEventsStatusMeta').val(thisStatus);
            } else {
                jQuery('#WooCommerceEventsStatusMeta').val('Not Checked In');
            }
        });
    }

})(jQuery);

(function ($) {

    function initAddToCalendarReminderRemove() {
        jQuery('.fooevents_add_to_calendar_reminders_remove').off('click');

        jQuery('.fooevents_add_to_calendar_reminders_remove').click(function (e) {
            e.preventDefault();

            jQuery(this).parent().remove();
        });
    }

    if (jQuery('#WooCommerceEventsTicketAddCalendarMeta').length) {
        initAddToCalendarReminderRemove();

        jQuery('#fooevents_add_to_calendar_reminders_new_field').click(function (e) {
            e.preventDefault();

            var reminderRow = jQuery('<span class="fooevents-add-to-calendar-reminder-row"></span>');

            reminderRow.append('<input type="number" min="0" step="1" name="WooCommerceEventsTicketAddCalendarReminderAmounts[]" value="10">');

            var reminderUnitsSelect = jQuery('<select name="WooCommerceEventsTicketAddCalendarReminderUnits[]"></select>');

            var minutesValue = "minutes";
            var hoursValue = "hours";
            var daysValue = "days";
            var weeksValue = "weeks";

            if ((typeof localRemindersObj === "object") && (localRemindersObj !== null)) {
                minutesValue = localRemindersObj.minutesValue;
                hoursValue = localRemindersObj.hoursValue;
                daysValue = localRemindersObj.daysValue;
                weeksValue = localRemindersObj.weeksValue;
            }

            reminderUnitsSelect.append('<option value="minutes" SELECTED>' + minutesValue + '</option>');
            reminderUnitsSelect.append('<option value="hours">' + hoursValue + '</option>');
            reminderUnitsSelect.append('<option value="days">' + daysValue + '</option>');
            reminderUnitsSelect.append('<option value="weeks">' + weeksValue + '</option>');

            reminderRow.append(reminderUnitsSelect);

            reminderRow.append('<a href="#" class="fooevents_add_to_calendar_reminders_remove">[X]</a>');

            jQuery('#fooevents_add_to_calendar_reminders_container').append(reminderRow);

            initAddToCalendarReminderRemove();
        });
    }

})(jQuery);
(function ($) {

    function setMultiDayDefaultColour() {
        // Background
        if (jQuery('input#WooCommerceEventsBackgroundColor').val() === '') {

            var eventType = jQuery('input[name="WooCommerceEventsType"]:checked').val();

            if (eventType === 'sequential' || eventType === 'select') {
                jQuery('input#WooCommerceEventsBackgroundColor').wpColorPicker('color', '#16A75D');
            } else if (eventType === 'bookings') {
                jQuery('input#WooCommerceEventsBackgroundColor').wpColorPicker('color', '#557DBD');
            } else if (eventType === 'seating') {
                jQuery('input#WooCommerceEventsBackgroundColor').val('').change();
            }
        }

        // Text
        if (jQuery('input#WooCommerceEventsTextColor').val() === '') {
            jQuery('input#WooCommerceEventsTextColor').wpColorPicker('color', '#FFFFFF');
        }
    }

    if (
        jQuery('input#WooCommerceEventsBackgroundColor').length &&
        jQuery('input#WooCommerceEventsTextColor').length &&
        jQuery('input[name="WooCommerceEventsType"]').length
    ) {
        jQuery('input[name="WooCommerceEventsType"]').change(function () {
            setMultiDayDefaultColour();
        });
    }

    setMultiDayDefaultColour();

})(jQuery);
(function ($) {

    if (jQuery('input[name=WooCommerceEventsType]').length) {

        var event_type = jQuery('input[name=WooCommerceEventsType]:checked').val();

        if (event_type == 'single') {

            fooevents_event_type_single_show();

        }
        if (event_type == 'select') {

            fooevents_event_type_select_show();

        }  
        if (event_type == 'seating') {

            fooevents_event_type_seating_show();

        } 
        if (event_type == 'sequential') {

            fooevents_event_type_sequential_show();

        }  
        if (event_type == 'bookings') {

            fooevents_event_type_bookings_show();

        }        

        jQuery('input[name=WooCommerceEventsType]').change(function () {

            var event_type = jQuery('input[name=WooCommerceEventsType]:checked').val();

            if (event_type == 'single') {

                fooevents_event_type_single_show();

            }
            if (event_type == 'seating') {

                fooevents_event_type_seating_show();

            }
            if (event_type == 'select') {
    
                fooevents_event_type_select_show();
    
            }  
            if (event_type == 'sequential') {
    
                fooevents_event_type_sequential_show();
    
            }  
            if (event_type == 'bookings') {
    
                fooevents_event_type_bookings_show();
    
            }  

        });

    }

    function fooevents_event_type_single_show() {


        // Hide non-single event type containers
        jQuery('.type_seating').hide();
        jQuery('.type_select').hide();
        jQuery('.type_sequential').hide();
        jQuery('.type_bookings').hide();

        // Show single event type containers
        jQuery('.type_single').show();
    }

    function fooevents_event_type_seating_show() {

        // Hide non-seating event type containers
        jQuery('.type_single').hide();    
        jQuery('.type_select').hide();
        jQuery('.type_sequential').hide();
        jQuery('.type_bookings').hide();    

        // Show seating event type containers
        jQuery('.type_seating').show();

    }

    function fooevents_event_type_select_show() {

        // Hide non-select event type containers
        jQuery('.type_single').hide();    
        jQuery('.type_seating').hide();
        jQuery('.type_sequential').hide();
        jQuery('.type_bookings').hide();   
        
        // Show select event type containers
        jQuery('.type_select').show();

    }

    function fooevents_event_type_sequential_show() {

        // Hide non-sequential event type containers
        jQuery('.type_single').hide();    
        jQuery('.type_seating').hide();
        jQuery('.type_select').hide();
        jQuery('.type_bookings').hide();  

        // Show sequential event type containers
        jQuery('.type_sequential').show();        

    }

    function fooevents_event_type_bookings_show() {

        // Hide non-bookings event type containers
        jQuery('.type_single').hide();    
        jQuery('.type_seating').hide();
        jQuery('.type_select').hide();  
        jQuery('.type_sequential').hide();    

        // Show bookings event type containers
        jQuery('.type_bookings').show(); 
        
    }

})(jQuery);
