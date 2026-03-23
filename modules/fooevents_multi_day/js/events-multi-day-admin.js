(function ($) {

    if (jQuery('input[name=WooCommerceEventsType]').length) {

        var event_type = jQuery('input[name=WooCommerceEventsType]:checked').val();

        if (event_type == 'select') {
            var changed = false;
            fooevents_event_type_select_show(changed);
            init_fooevents_update_select_start_end_times();
            init_fooevents_period_validation();
            fooevents_update_select_start_end_times();
        }

        jQuery('input[name=WooCommerceEventsType]').change(function () {
            var event_type = jQuery('input[name=WooCommerceEventsType]:checked').val();
            if (event_type == 'select') {
                var changed = true;
                fooevents_event_type_select_show(changed);
            }
        });

        jQuery('#fooevents_options').on("change", "#WooCommerceEventsNumDays", function () {
            fooevents_display_select_date_inputs(localObjMultiDay.dayTerm, localObjMultiDay.startTimeTerm, localObjMultiDay.endTimeTerm);
        });

    }

    jQuery('#post').submit(function (e) {
        jQuery('.WooCommerceEventsSelectDatePeriod').prop('disabled', false);
        jQuery('.WooCommerceEventsSelectDatePeriodEnd').prop('disabled', false);
    });

    function fooevents_event_type_select_show(changed) {
        

        if (jQuery('#WooCommerceEventsSelectGlobalTime').is(':checked')) {
            jQuery('.WooCommerceEventsSingleGroup').show();
            jQuery('#WooCommerceEventsTimeContainer').show();
            jQuery('#WooCommerceEventsEndTimeContainer').show();
        } else {
            jQuery('.WooCommerceEventsSingleGroup').hide();
            jQuery('#WooCommerceEventsTimeContainer').hide();
            jQuery('#WooCommerceEventsEndTimeContainer').hide();
        }

        if (changed) {
            fooevents_display_select_date_inputs(localObjMultiDay.dayTerm, localObjMultiDay.startTimeTerm, localObjMultiDay.endTimeTerm);
        }

    }

    function fooevents_display_select_date_inputs(dayTerm, startTimeTerm, endTimeTerm) {

        if (jQuery('input[name="WooCommerceEventsType"]:checked').val() != 'select') {
            return;
        }

        jQuery('#WooCommerceEventsSelectDateContainer').show();

        var numCurrentDays = parseInt(jQuery('.WooCommerceEventsSelectDateDay').length);
        var numSelectedDays = parseInt(jQuery('#WooCommerceEventsNumDays').val());

        if (numCurrentDays < numSelectedDays) {

            var difDays = numSelectedDays - numCurrentDays;

            var nextNum = numCurrentDays + 1;

            var dateFields = '';
            for (var i = 1; i <= difDays; i++) {

                dateFields += '<div class="WooCommerceEventsSelectDateDay">';
                dateFields += '<p class="form-field">';
                dateFields += '<label>' + dayTerm + ' ' + nextNum + ':</label>';
                dateFields += '<input type="text" class="WooCommerceEventsSelectDate" name="WooCommerceEventsSelectDate[]" id="WooCommerceEventsSelectDate-' + nextNum + '" value=""/>';
                dateFields += '</p>';

                dateFields += '<p class="form-field WooCommerceEventsSelectDateTimeContainer">';
                dateFields += '<label>' + startTimeTerm + ':</label>';
                dateFields += '<select name="WooCommerceEventsSelectDateHour[]" class="WooCommerceEventsSelectDateHour" id="WooCommerceEventsSelectDateHour-' + nextNum + '">';
                for (var y = 0; y <= 23; y++) {
                    y = ('0' + y).slice(-2)
                    dateFields += '<option value="' + y + '">' + y + '</option>';
                }
                dateFields += '</select>';

                dateFields += '<select name="WooCommerceEventsSelectDateMinutes[]" class="WooCommerceEventsSelectDateMinutes" id="WooCommerceEventsSelectDateMinutes-' + nextNum + '">';
                for (var y = 0; y <= 59; y++) {
                    y = ('0' + y).slice(-2)
                    dateFields += '<option value="' + y + '">' + y + '</option>';
                }
                dateFields += '</select>';

                dateFields += '<select name="WooCommerceEventsSelectDatePeriod[]" class="WooCommerceEventsSelectDatePeriod" id="WooCommerceEventsSelectDatePeriod-' + nextNum + '">';
                dateFields += '<option value="">-</option>';
                dateFields += '<option value="a.m.">a.m.</option>';
                dateFields += '<option value="p.m.">p.m.</option>';
                dateFields += '</select>';

                dateFields += '</p>';

                dateFields += '<p class="form-field WooCommerceEventsSelectDateTimeContainer">';
                dateFields += '<label>' + endTimeTerm + ':</label>';
                dateFields += '<select name="WooCommerceEventsSelectDateHourEnd[]" class="WooCommerceEventsSelectDateHourEnd" id="WooCommerceEventsSelectDateHourEnd-' + nextNum + '">';
                for (var y = 0; y <= 23; y++) {
                    y = ('0' + y).slice(-2)
                    dateFields += '<option value="' + y + '">' + y + '</option>';
                }
                dateFields += '</select>';

                dateFields += '<select name="WooCommerceEventsSelectDateMinutesEnd[]" class="WooCommerceEventsSelectDateMinutesEnd" id="WooCommerceEventsSelectDateMinutesEnd-' + nextNum + '">';
                for (var y = 0; y <= 59; y++) {
                    y = ('0' + y).slice(-2)
                    dateFields += '<option value="' + y + '">' + y + '</option>';
                }
                dateFields += '</select>';

                dateFields += '<select name="WooCommerceEventsSelectDatePeriodEnd[]" class="WooCommerceEventsSelectDatePeriodEnd" id="WooCommerceEventsSelectDatePeriodEnd-' + nextNum + '" >';
                dateFields += '<option value="">-</option>';
                dateFields += '<option value="a.m.">a.m.</option>';
                dateFields += '<option value="p.m.">p.m.</option>';
                dateFields += '</select>';

                dateFields += '</p>';

                dateFields += '</div>';

                nextNum++;

            }

            jQuery('#WooCommerceEventsSelectDateContainer').append(dateFields);


        }

        if (numCurrentDays > numSelectedDays) {

            var difDays = numCurrentDays - numSelectedDays;

            for (var i = 1; i <= difDays; i++) {

                jQuery('.WooCommerceEventsSelectDateDay').last().remove();

            }

        }

        init_fooevents_select_datepickers();
        init_fooevents_period_validation();
        init_fooevents_update_select_start_end_times();

    }

    function init_fooevents_update_select_start_end_times() {

        jQuery('#fooevents_options').on("change", ".WooCommerceEventsGlobalTime, #WooCommerceEventsSelectGlobalTime", function () {

            fooevents_update_select_start_end_times();
            if (jQuery('#WooCommerceEventsSelectGlobalTime').is(':checked')) {
                jQuery('.WooCommerceEventsSingleGroup').show(); 
                jQuery('#WooCommerceEventsTimeContainer').show();
                jQuery('#WooCommerceEventsEndTimeContainer').show();
            } else {
                jQuery('.WooCommerceEventsSingleGroup').hide();
                jQuery('#WooCommerceEventsTimeContainer').hide();
                jQuery('#WooCommerceEventsEndTimeContainer').hide();
            }

            
        });
    }

    function fooevents_update_select_start_end_times() {

        if (jQuery('#WooCommerceEventsSelectGlobalTime').is(':checked')) {

            var WooCommerceEventsHour = jQuery('#WooCommerceEventsHour').val();

            if (WooCommerceEventsHour != '00') {

                jQuery('.WooCommerceEventsSelectDateHour').val(WooCommerceEventsHour); 

            }

            var WooCommerceEventsMinutes = jQuery('#WooCommerceEventsMinutes').val();

            if (WooCommerceEventsMinutes != '00') {

                jQuery('.WooCommerceEventsSelectDateMinutes').val(WooCommerceEventsMinutes);

            }

            var WooCommerceEventsPeriod = jQuery('#WooCommerceEventsPeriod').val();

            if (WooCommerceEventsPeriod != '') {

                jQuery('.WooCommerceEventsSelectDatePeriod').val(WooCommerceEventsPeriod);

            }

            if (jQuery('#WooCommerceEventsPeriod').is(':disabled')) {

                jQuery('.WooCommerceEventsSelectDatePeriod').val('').prop('disabled', true);

            } else {

                jQuery('.WooCommerceEventsSelectDatePeriod').prop('disabled', false);

            }

            var WooCommerceEventsHourEnd = jQuery('#WooCommerceEventsHourEnd').val();

            if (WooCommerceEventsHourEnd != '00') {

                jQuery('.WooCommerceEventsSelectDateHourEnd').val(WooCommerceEventsHourEnd);

            }

            var WooCommerceEventsMinutesEnd = jQuery('#WooCommerceEventsMinutesEnd').val();

            if (WooCommerceEventsMinutesEnd != '00') {

                jQuery('.WooCommerceEventsSelectDateMinutesEnd').val(WooCommerceEventsMinutesEnd);

            }

            var WooCommerceEventsEndPeriod = jQuery('#WooCommerceEventsEndPeriod').val();

            if (WooCommerceEventsEndPeriod != '') {

                jQuery('.WooCommerceEventsSelectDatePeriodEnd').val(WooCommerceEventsEndPeriod);

            }

            if (jQuery('#WooCommerceEventsEndPeriod').is(':disabled')) {

                jQuery('.WooCommerceEventsSelectDatePeriodEnd').val('').prop('disabled', true);

            } else {

                jQuery('.WooCommerceEventsSelectDatePeriodEnd').prop('disabled', false);

            }

            jQuery(".WooCommerceEventsSelectDateTimeContainer").hide();

        } else {

            jQuery(".WooCommerceEventsSelectDateTimeContainer").show();

        }

    }

    function init_fooevents_period_validation() {

        jQuery('#fooevents_options').on("change", ".WooCommerceEventsSelectDateHour, .WooCommerceEventsSelectDateHourEnd", function () {

            var changed_id = jQuery(this).attr('id');
            var changed_id = changed_id.split("-");;

            if (parseInt(jQuery(this).val()) > 12) {

                jQuery('#WooCommerceEventsSelectDatePeriod-' + changed_id[1]).val('').prop('disabled', true);
                jQuery('#WooCommerceEventsSelectDatePeriodEnd-' + changed_id[1]).val('').prop('disabled', true);

            } else {

                jQuery('#WooCommerceEventsSelectDatePeriod-' + changed_id[1]).prop('disabled', false);
                jQuery('#WooCommerceEventsSelectDatePeriodEnd-' + changed_id[1]).prop('disabled', false);

            }

        });

    }

    function init_fooevents_select_datepickers() {

        if ((typeof localObjMultiDay === "object") && (localObjMultiDay !== null)) {

            jQuery('.WooCommerceEventsSelectDate').datepicker({
                showButtonPanel: true,
                closeText: localObjMultiDay.closeText,
                currentText: localObjMultiDay.currentText,
                monthNames: localObjMultiDay.monthNames,
                monthNamesShort: localObjMultiDay.monthNamesShort,
                dayNames: localObjMultiDay.dayNames,
                dayNamesShort: localObjMultiDay.dayNamesShort,
                dayNamesMin: localObjMultiDay.dayNamesMin,
                dateFormat: localObjMultiDay.dateFormat,
                firstDay: localObjMultiDay.firstDay,
                isRTL: localObjMultiDay.isRTL,
            });

        } else {

            jQuery('.WooCommerceEventsSelectDate').datepicker();

        }

    }

    init_fooevents_select_datepickers();

})(jQuery);