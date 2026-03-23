(function ($) {

    var typing_timer;
    var done_typing_interval = 20;

    fooevents_bookings_datepicker();
    fooevents_bookings_options_table_sortable();
    fooevents_bookings_add_date_table_sortable();
    fooevents_bookings_time_validation();
    fooevents_bookings_show_hide_settings_tab();

    jQuery('#fooevents_bookings_options_table').on('change', '.fooevents_bookings_label, .fooevents_bookings_date, .fooevents_bookings_date_stock, .fooevents_bookings_zoom_id, .fooevents_bookings_zoom_id_select, .fooevents_bookings_add_time, .fooevents_bookings_time_field', function (event) {

        fooevents_bookings_validate_characters(jQuery(this));
        clearTimeout(typing_timer);

        typing_timer = setTimeout(fooevents_bookings_serialize_options, done_typing_interval, jQuery(this));

        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_remove', function (event) {

        if (confirm(FooEventsBookingsProdObj.removeSlot)) {

            fooevents_delete_bookings_row(jQuery(this));

        }

        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_date_remove', function (event) {

        fooevents_delete_date_row(jQuery(this));

        return false;

    });


    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_copy', function (event) {

        fooevents_bookings_copy_row(jQuery(this));
        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_toggle', function (event) {

        fooevents_bookings_toggle_row(jQuery(this));

        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_add_time', function (event) {

        fooevents_add_time_options(event, jQuery(this));

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_zoom_id', function (event) {

        fooevents_zoom_id_options(event, jQuery(this));

    });

    jQuery('.fooevents_bookings_expand_all').on('click', function () {

        fooevents_bookings_expand_all_row();

        return false;

    });

    jQuery('.fooevents_bookings_close_all').on('click', function () {

        fooevents_bookings_close_all_row();

        return false;

    });

    jQuery('#fooevents_bookings_save').on('click', function () {

        jQuery(this).text(FooEventsBookingsProdObj.savingChanges + '...').attr('disabled', 'disabled');

        fooevents_save_booking_options();
        return false;

    });

    jQuery('#fooevents_bookings_new_field').on('click', function () {

        fooevents_new_booking_field();
        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents_bookings_add_date', function (event) {

        fooevents_add_date_row(jQuery(this));

        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('change', '.fooevents_bookings_time_hour', function (event) {

        fooevents_bookings_time_validation_now(event);

        return false;

    });

    jQuery('#fooevents_options').on('change', 'input[name="WooCommerceEventsType"]', function (event) {

        fooevents_bookings_show_hide_settings_tab(event);

        return false;

    });

    jQuery('#fooevents_bookings_options_table').on('keypress', '.fooevents_bookings_date, .fooevents_bookings_label, .fooevents_bookings_date_stock', function (event) {

        if (event.keyCode == 13) {

            event.preventDefault();

        }

    });

    jQuery('#post').submit(function (e) {

        jQuery('.fooevents_bookings_wrap').remove();

    });

    if ($('input[name=WooCommerceEventsBookingsExpirePassedDate]').is(':checked')) {

        jQuery('#WooCommerceEventsBookingsExpireValue, #WooCommerceEventsBookingsExpireUnit').prop('disabled', false);

    } else {

        jQuery('#WooCommerceEventsBookingsExpireValue, #WooCommerceEventsBookingsExpireUnit').prop('disabled', true);

    }

    jQuery('input[name=WooCommerceEventsBookingsExpirePassedDate]').change(function () {

        if (this.checked) {

            jQuery('#WooCommerceEventsBookingsExpireValue, #WooCommerceEventsBookingsExpireUnit').prop('disabled', false);

        } else {

            jQuery('#WooCommerceEventsBookingsExpireValue, #WooCommerceEventsBookingsExpireUnit').prop('disabled', true);

        }

    });

    jQuery("#fooevents_bookings_options_table tr").hover(

        function () {
            jQuery(this).find(".booking_options").fadeIn("fast");
        },

        function () {
            jQuery(this).find(".booking_options").fadeOut("fast");
        }

    );

    jQuery('.fooevents_bookings_settings').on('click', function (event) {

        jQuery(this).closest('.fooevents_booking_options_column').find('.booking_options').toggle();

    });

    dialog = jQuery("#fooevents-dialog-add-dates").dialog({
        autoOpen: false,
        height: 410,
        width: 550,
        modal: true,
        closeText: "",
        dialogClass: "fooevents-dialog-add-dates-container",
        buttons: {
            "fooevents-generate-single-date": {
                text: "Add Single Date",
                id: "fooevents-generate-single-date",
                class: "button",
                click: fooevents_generate_wizard_dates_single,
            },
            "fooevents-or-text": {
                text: "OR",
                id: "fooevents-or-text",
                class: "ui-state-disabled",
                click: function () { }
            },
            "fooevents-generate-dates": {
                text: "Generate Multiple Dates",
                id: "fooevents-generate-dates",
                class: 'button-primary',
                click: function () {

                    var passed = fooevents_validate_wizard_fields();

                    if (passed) {

                        jQuery("#fooevents_bookings_date").prop('disabled', true);
                        jQuery("#fooevents-generate-dates").prop('disabled', true);
                        jQuery("#fooevents-generate-single-date").prop('disabled', true);
                        jQuery(".fooevents-dialog-add-dates-loading").show();

                        jQuery("#fooevents_bookings_date").prop('disabled', true);

                        var parent_id = jQuery("#fooevents-dialog-add-dates").data('parent_id');

                        var start_date_option = jQuery('#fooevents_wizard_start_date').val().replace(/\./g, '');
                        var end_date_option = jQuery('#fooevents_wizard_end_date').val().replace(/\./g, '');
                        var stock_option = jQuery('#fooevents_wizard_stock').val();

                        start_date_option = fooevents_clean_date(start_date_option, FooEventsBookingsProdObj.dateFormat);
                        end_date_option = fooevents_clean_date(end_date_option, FooEventsBookingsProdObj.dateFormat);

                        var week_days_option = new Array();

                        jQuery.each(jQuery("input[name='fooevents_wizard_week_day']:checked"), function () {

                            week_days_option.push(jQuery(this).val());

                        });

                        var start_date_processed = fooevents_js_convert_month_to_english(start_date_option, FooEventsBookingsProdObj.monthNames);
                        var end_date_processed = fooevents_js_convert_month_to_english(end_date_option, FooEventsBookingsProdObj.monthNames);

                        var start_date = new Date(start_date_processed);
                        var end_date = new Date(end_date_processed);

                        var days_to_add = [];

                        if (start_date && end_date && days_to_add) {

                            setTimeout(
                                function () {
                                    fooevents_generate_wizard_dates(start_date, end_date, days_to_add, week_days_option, stock_option, parent_id);
                                }, 1000);

                        }

                        return true;

                    } else {

                        return false;
                    }

                },
            },
        },
        close: function () {
        },
        beforeClose: function () {
            jQuery("#fooevents_bookings_date").prop('disabled', false);
            jQuery("#fooevents-generate-dates").prop('disabled', false);
            jQuery("#fooevents-generate-single-date").prop('disabled', false);
            jQuery(".fooevents-dialog-add-dates-loading").hide();

            jQuery("#fooevents_bookings_date").prop('disabled', false);
        }
    });

    function fooevents_clean_date(date, date_format) {

        if (date_format == "dd/mm/yy") {

            var d_arr = date.split("/");
            var newdate = d_arr[1] + "/" + d_arr[0] + "/" + d_arr[2];
            return newdate;

        }

        return date;
    }

    jQuery('#fooevents_bookings_options_table').on('click', '.fooevents-add-dates', function (event) {

        event.preventDefault();

        dialog.data('parent_id', this).dialog("open");

    });

    function fooevents_js_convert_month_to_english(str, findArray) {

        var replaceArray = ['January ', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        var i, regex = [], map = {};
        for (i = 0; i < findArray.length; i++) {
            regex.push(findArray[i].replace(/([-[\]{}()*+?.\\^$|#,])/g, '\\$1'));
            map[findArray[i]] = replaceArray[i];
        }
        regex = regex.join('|');
        str = str.replace(new RegExp(regex, 'g'), function (matched) {
            return map[matched];
        });
        return str;

    }

    function fooevents_bookings_show_hide_settings_tab(event) {

        var event_type = jQuery('input[name="WooCommerceEventsType"]:checked').val();

        if (event_type == 'bookings') {

            jQuery('#custom_tab_booking_options').show();

        } else {

            jQuery('#custom_tab_booking_options').hide();

        }

    }

    function fooevents_add_time_options(event, time_option) {

        var parent_result = jQuery(time_option).attr('id').split('_');

        if (time_option.is(':checked')) {

            var hours = '<select name="hour" data-bookings="hour" id="' + parent_result[0] + '_hour" class="fooevents_bookings_time_field fooevents_bookings_time_hour">';

            var i;
            for (i = 0; i <= 24; i++) {

                var hour_output = ('0' + i).slice(-2)
                hours += '<option value="' + hour_output + '">' + hour_output + '</option>';

            }

            hours += '</select>';

            var minutes = '<select data-bookings="minute" name="minute" id="' + parent_result[0] + '_minute" class="fooevents_bookings_time_field fooevents_bookings_time_minute">';

            var i;
            for (i = 0; i <= 59; i++) {

                var minute_val = ("0" + i).slice(-2);
                minutes += '<option value="' + minute_val + '">' + minute_val + '</option>';

            }

            minutes += '</select>';

            var period = '<select data-bookings="period" name="period" id="' + parent_result[0] + '_period" class="fooevents_bookings_time_field fooevents_bookings_time_period"><option value="">-</option><option value="a.m.">a.m.</option><option value="p.m.">p.m.</option></select>';

            jQuery('#' + parent_result[0] + '_add_time_column').html(hours + minutes + period);

        } else {

            jQuery('#' + parent_result[0] + '_add_time_column').html('');

        }



    }

    function fooevents_zoom_id_options(event, time_option) {

        var parent_result = jQuery(time_option).attr('id').split('_');
        var parent_id = parent_result[0];
        var holder = '#' + parent_id + '_holder';

        if (time_option.is(':checked')) {

            if (!jQuery('#' + parent_result[0] + '_add_time').is(':checked')) {

                jQuery('#' + parent_result[0] + '_add_time').trigger('click');

            }

            jQuery('#' + parent_result[0] + '_add_time').prop('disabled', true);

            jQuery(holder).find('.fooevents_bookings_zoom_id_select').val('').removeClass('bookings-exclude').trigger('change');
            jQuery(holder).find('.fooevents_bookings_zoom_id_container').show();

        } else {

            jQuery('#' + parent_result[0] + '_add_time').prop('disabled', false);

            jQuery(holder).find('.fooevents_bookings_zoom_id_select').val('').addClass('bookings-exclude').trigger('change');
            jQuery(holder).find('.fooevents_bookings_zoom_id_container').hide();

        }



    }

    function fooevents_bookings_toggle_row(parent_row) {

        var parent_result = jQuery(parent_row).attr('id').split('_');
        var parent_id = parent_result[0];
        var holder = '#' + parent_id + '_holder';

        jQuery(holder).slideToggle();

        parent_row.toggleClass("dashicons-arrow-up dashicons-arrow-down");

    }

    function fooevents_bookings_copy_row(parent_row) {

        var booking_id = fooevents_bookings_make_id(20);
        var clone_slot = parent_row.closest("tr").clone();

        if(booking_id) {

            clone_slot.attr("id", booking_id);
            clone_slot.find('.fooevents_booking_handle_column').attr("id", booking_id + "_handle_column");
            clone_slot.find('.fooevents_booking_label_column').attr("id", booking_id + "_label_column");
            clone_slot.find('.fooevents_booking_add_time_column').attr("id", booking_id + "_add_time_column");
            clone_slot.find('.fooevents_booking_options_column').attr("id", booking_id + "_options_column");
            clone_slot.find('.fooevents_bookings_label').attr("id", booking_id + "_label");
            clone_slot.find('.fooevents_bookings_add_date').attr("id", booking_id + "_add_date");
            clone_slot.find('.fooevents_bookings_zoom_id').attr("id", booking_id + "_zoom_id");
            clone_slot.find('.fooevents_zoom_id_label').attr("for", booking_id + "_zoom_id");
            clone_slot.find('.fooevents_bookings_add_time').attr("id", booking_id + "_add_time");
            clone_slot.find('.fooevents_add_time_label').attr("for", booking_id + "_add_time");
            clone_slot.find('.fooevents_bookings_remove').attr("id", booking_id + "_remove");
            clone_slot.find('.fooevents_bookings_copy').attr("id", booking_id + "_copy");
            clone_slot.find('.fooevents_bookings_toggle').attr("id", booking_id + "_toggle");

            clone_slot.find('.fooevents_bookings_time_hour').attr("id", booking_id + "_hour");
            clone_slot.find('.fooevents_bookings_time_minute').attr("id", booking_id + "_minute");
            clone_slot.find('.fooevents_bookings_time_period').attr("id", booking_id + "_period");

            var selects = parent_row.closest("tr").find("select");
            jQuery(selects).each(function (i) {
                var select = this;
                clone_slot.find("select").eq(i).val($(select).val());
            });

            var date_table = clone_slot.find('.fooevents_bookings_add_date_table').find('tr');
            date_table.each(function (index, tr) {

                var date_id = fooevents_bookings_make_id(20);
                jQuery(tr).find('.fooevents_bookings_date').data("bookings", date_id + '_add_date');
                jQuery(tr).find('.fooevents_bookings_date').attr("id", "").removeClass('hasDatepicker').removeData('datepicker').unbind();
                jQuery(tr).find('.fooevents_bookings_zoom_id_container').attr("id", date_id + '_zoom_id_container');
                jQuery(tr).find('.fooevents_bookings_zoom_id_select').attr("id", date_id + '_zoom_id').data("bookings", date_id + '_zoom_id');
                jQuery(tr).find('.fooevents_bookings_date_stock').data("bookings", date_id + '_stock');
                jQuery(tr).find('.fooevents_bookings_date_remove').attr("id", date_id + '_remove');

            });

            clone_slot.find('.fooevents_bookings_add_date_table').attr("id", booking_id + "_holder");

            clone_slot.insertAfter(parent_row.closest("tr"));

            fooevents_bookings_serialize_options();
            fooevents_bookings_datepicker();
            fooevents_bookings_insert_zoom_dropdowns();

        } else {

            alert(FooEventsBookingsProdObj.idGenError);

        }

    }

    function fooevents_bookings_expand_all_row() {

        jQuery('.fooevents_bookings_add_date_table').show();
        jQuery('.fooevents_bookings_toggle').removeClass("dashicons-arrow-down");
        jQuery('.fooevents_bookings_toggle').removeClass("dashicons-arrow-up");
        jQuery('.fooevents_bookings_toggle').addClass("dashicons-arrow-up");

    }

    function fooevents_bookings_close_all_row() {

        jQuery('.fooevents_bookings_add_date_table').hide();
        jQuery('.fooevents_bookings_toggle').removeClass("dashicons-arrow-up");
        jQuery('.fooevents_bookings_toggle').removeClass("dashicons-arrow-down");
        jQuery('.fooevents_bookings_toggle').addClass("dashicons-arrow-down");

    }

    function fooevents_new_booking_field() {

        fooevents_bookings_validate_options();

        jQuery('.fooevents_bookings_none').hide();

        var multi_day_options = null;
        var multi_day = false;

        var booking_id = fooevents_bookings_make_id(20);

        if (booking_id) {

            var opt_num = jQuery('#fooevents_bookings_options_table tr').length;
            var label = '<input type="text" id="' + booking_id + '_label" data-bookings="label" class="fooevents_bookings_label" value="Label_' + opt_num + '" autocomplete="off" maxlength="50" />';
            var add_dates_button = '<button class="fooevents-add-dates button" id="' + booking_id + '_fooevents-add-dates">Add dates</button>';
            var date_options = '<label for="' + booking_id + '_zoom_id" class="fooevents_zoom_id_label" style="display:' + (jQuery('input[name="WooCommerceEventsZoomMultiOption"]:checked').val() == 'bookings' ? 'inline' : 'none') + ';"><input type="checkbox" id="' + booking_id + '_zoom_id" data-bookings="zoom_id" name="zoom_id" value="enabled" class="fooevents_bookings_zoom_id"/> Zoom</label><label for="' + booking_id + '_add_time" class="fooevents_add_time_label"><input type="checkbox" id="' + booking_id + '_add_time" data-bookings="add_time" name="add_time" value="enabled" class="fooevents_bookings_add_time"/> ' + FooEventsBookingsProdObj.time + '</label> ' + add_dates_button;
            var remove = '<a href="#" id="' + booking_id + '_copy" class="fooevents_bookings_copy dashicons-before dashicons-admin-page"></a><a href="#" id="' + booking_id + '_remove" name="remove" class="fooevents_bookings_remove dashicons-before dashicons-trash"></a><span></span>';

            var new_field = '<tr id="' + booking_id + '" class="fooevents_bookings_option"><td colspan="2"><div class="fooevents_booking_col fooevents_booking_col_1 "><span class="indent"><span class="dashicons dashicons-menu fooevents_bookings_handle"></span></span></div><div class="fooevents_booking_col fooevents_booking_col_2" id="' + booking_id + '_label_column">' + label + '</div><div class="fooevents_booking_col fooevents_booking_col_2 fooevents_booking_col_time fooevents_booking_add_time_column" id="' + booking_id + '_add_time_column"></div><div class="fooevents_booking_col fooevents_booking_col_3 fooevents_booking_options_column" id="' + booking_id + '_options_column"><a href="javascript:void(0);" id="' + booking_id + '_toggle" class="dashicons-before dashicons-arrow-up fooevents_bookings_toggle"></a><div class="fooevents_booking_col fooevents_booking_col_6 booking_options booking_options_new" style="display: block">' + date_options + ' ' + remove + '</div></div><table class="fooevents_bookings_add_date_table" cellspacing="0" cellpadding="0" id="' + booking_id + '_holder"><tbody></tbody></table></td></tr>';

            jQuery('#fooevents_bookings_options_table tbody:not(tbody tbody)').append(new_field);

            fooevents_bookings_datepicker();
            fooevents_bookings_serialize_options();

        } else {

            alert(FooEventsBookingsProdObj.idGenError);

        }

    }

    function fooevents_add_date_row(parent_row, set_booking_date = '', stock_option = '') {

        fooevents_bookings_validate_options();

        var parent_result = jQuery(parent_row).attr('id').split('_');
        var parent_id = parent_result[0];
        var holder = '#' + parent_id + '_holder';

        var date_id = fooevents_bookings_make_id(20);

        if(date_id) {

            var date_options = '<input type="text" data-bookings="' + date_id + '_add_date" class="WooCommerceEventsBookingsSelectDate fooevents_bookings_date" value="' + set_booking_date + '"/>';
            var zoom_id = '<span id="' + date_id + '_zoom_id_container" class="fooevents_bookings_zoom_id_container" data-zoom-id="" style="display:' + (jQuery('#' + parent_id + '_zoom_id:checked').length ? 'inline' : 'none') + ';"></span> ';
            var stock = '<input type="number" min="0" id="' + date_id + '_stock" data-bookings="' + date_id + '_stock" class="fooevents_bookings_date_stock" autocomplete="off" maxlength="10" placeholder="' + FooEventsBookingsProdObj.unlimitedStock + '" value="' + stock_option + '" /> ';
            var remove = ' <a href="#" id="' + date_id + '_remove" name="' + date_id + '_remove" class="fooevents_bookings_date_remove">[X]</a>';
            var new_date_row = '<tr><td width="10%" class="fooevents_bookings_handle_container"><span class="dashicons dashicons-menu fooevents_bookings_handle"></span></td><td width="90%">' + date_options + ' ' + zoom_id + ' ' + stock + ' ' + remove + '</td></tr>';

            jQuery(holder).append(new_date_row);

            fooevents_bookings_datepicker();
            fooevents_bookings_insert_zoom_dropdowns();
            fooevents_bookings_add_date_table_sortable();
            fooevents_bookings_serialize_options();
            jQuery(holder).show();

        } else {

            alert(FooEventsBookingsProdObj.idGenError);

        }

    }

    function fooevents_generate_wizard_dates_single(event) {

        var start_date_option = jQuery('#fooevents_wizard_start_date').val();
        var parent_id = jQuery("#fooevents-dialog-add-dates").data('parent_id');
        jQuery('#fooevents-dialog-add-dates').dialog('close');

        fooevents_add_date_row(parent_id, start_date_option);

        return true;

    }

    function fooevents_generate_wizard_dates(start_date, end_date, days_to_add, week_days_option, stock_option, parent_id) {

        var i = start_date;

        function date_loop() {
            setTimeout(function () {

                if (jQuery.inArray("Mon", week_days_option) !== -1 && i.getDay() == 1) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Tues", week_days_option) !== -1 && i.getDay() == 2) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Weds", week_days_option) !== -1 && i.getDay() == 3) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Thurs", week_days_option) !== -1 && i.getDay() == 4) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Fri", week_days_option) !== -1 && i.getDay() == 5) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Sat", week_days_option) !== -1 && i.getDay() == 6) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                } else if (jQuery.inArray("Sun", week_days_option) !== -1 && i.getDay() == 0) {

                    var returned_date = jQuery.datepicker.formatDate(FooEventsBookingsProdObj.dateFormat, i);
                    fooevents_add_date_row(parent_id, returned_date, stock_option);

                }

                i.setTime(i.getTime() + 1000 * 60 * 60 * 24);
                if (i < end_date) {

                    date_loop();

                } else {

                    jQuery('#fooevents-dialog-add-dates').dialog('close');

                }
            }, 100)
        }

        date_loop();

    }

    function fooevents_validate_wizard_fields() {

        var start_date_option = jQuery('#fooevents_wizard_start_date').val();

        if (!start_date_option) {

            alert(FooEventsBookingsProdObj.startDateRequired);
            return false;

        }

        var end_date_option = jQuery('#fooevents_wizard_end_date').val();

        if (!end_date_option) {

            alert(FooEventsBookingsProdObj.endDateRequired);
            return false;

        }

        var start_date = new Date(start_date_option);
        var end_date = new Date(end_date_option);

        var Difference_In_Time = end_date.getTime() - start_date.getTime();

        var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

        if (Difference_In_Days > 186) {

            alert(FooEventsBookingsProdObj.sixMonthLimit);
            return false;

        }

        var week_days_option = new Array();

        jQuery.each(jQuery("input[name='fooevents_wizard_week_day']:checked"), function () {

            week_days_option.push(jQuery(this).val());

        });

        if (week_days_option.length === 0) {

            alert(FooEventsBookingsProdObj.weekDayRequired);
            return false;

        }

        return true;

    }

    function fooevents_delete_bookings_row(row) {

        row.closest('tr').remove();
        fooevents_bookings_serialize_options();

    }

    function fooevents_delete_date_row(row) {

        var parent_add_date_table = row.closest('.fooevents_bookings_add_date_table').attr('id');
        var num_added_dates = jQuery('#' + parent_add_date_table + ' tr').length;

        if (num_added_dates <= 1) {

            alert('Slot requires at least one date.');

        } else {

            var zoomEnabled = row.closest('.fooevents_bookings_add_date_table').parent().find('.fooevents_bookings_zoom_id:checked').length;

            if (zoomEnabled) {

                var zoomID = row.closest('tr').find('select.fooevents_bookings_zoom_id_select').val();

                if (zoomID != '' && zoomID != 'auto') {

                    if (confirm(FooEventsBookingsProdObj.deleteZoom)) {

                        fooevents_bookings_delete_zoom(zoomID);

                    }

                }

            }

            row.closest('tr').remove();

        }

        fooevents_bookings_serialize_options();

    }

    function fooevents_bookings_datepicker() {

        if ((typeof FooEventsBookingsProdObj === "object") && (FooEventsBookingsProdObj !== null)) {

            jQuery('.WooCommerceEventsBookingsSelectDate').datepicker({
                showButtonPanel: true,
                closeText: FooEventsBookingsProdObj.closeText,
                currentText: FooEventsBookingsProdObj.currentText,
                monthNames: FooEventsBookingsProdObj.monthNames,
                monthNamesShort: FooEventsBookingsProdObj.monthNamesShort,
                dayNames: FooEventsBookingsProdObj.dayNames,
                dayNamesShort: FooEventsBookingsProdObj.dayNamesShort,
                dayNamesMin: FooEventsBookingsProdObj.dayNamesMin,
                dateFormat: FooEventsBookingsProdObj.dateFormat,
                firstDay: FooEventsBookingsProdObj.firstDay,
                isRTL: FooEventsBookingsProdObj.isRTL,
            });

        } else {

            jQuery('.WooCommerceEventsBookingsSelectDate').datepicker();

        }

    }

    function fooevents_bookings_options_table_sortable() {

        jQuery('table#fooevents_bookings_options_table tbody').sortable({

            handle: '.fooevents_bookings_handle',
            update: function () {
                fooevents_bookings_serialize_options();
            }

        });

    }

    function fooevents_bookings_add_date_table_sortable() {

        jQuery('table.fooevents_bookings_add_date_table tbody').sortable({

            handle: '.fooevents_bookings_handle',
            update: function () {

                fooevents_bookings_serialize_options();

            }

        });

    }

    function fooevents_bookings_serialize_options() {

        var data = {};
        var item_num = 0;
        jQuery('#fooevents_bookings_options_table').find('tr:not(tr tr)').each(function () {

            var id = jQuery(this).attr('id');
            if (item_num) {
                var row = {};
                jQuery(this).find('input:text,input:checkbox:checked,select,textarea,.fooevents_bookings_date_stock:input').each(function () {
                    if (!jQuery(this).hasClass('bookings-exclude')) {
                        row[jQuery(this).data('bookings')] = jQuery(this).val();
                    }
                });
                data[id] = row;

            }

            item_num++;
        });

        data = JSON.stringify(data);

        jQuery('#fooevents_bookings_options_serialized').val(data);

    }

    function fooevents_bookings_make_id(length = 20) {

        var result = '';
        var characters = 'abcdefghijklmnopqrstuvwxyz';
        var charactersLength = characters.length;

        for (var i = 0; i < length; i++) {

            result += characters.charAt(Math.floor(Math.random() * charactersLength));

        }

        return result;

    }

    function fooevents_bookings_validate_characters(field) {

        var field_to_validate = field.attr('id');
        var field_value = field.val();

        if (field_value.includes("'") || field_value.includes('"')) {

            var clean_string = field_value.replace(/['"\s]/g, "");
            clean_string.trim();
            field.val(clean_string);

            alert(FooEventsBookingsProdObj.illegalCharacter);
        }

        //alert(field_value);

    }

    function fooevents_bookings_validate_options() {

        jQuery(".fooevents_bookings_label").each(function () {

            var value = jQuery(this).val();

            if (value === "") {

                jQuery(this).closest('td input').css("border-color", "#a24a4a");

            }

        });

        jQuery(".fooevents_bookings_date").each(function () {

            var value = jQuery(this).val();

            if (value === "") {

                jQuery(this).closest('td input').css("border-color", "#a24a4a");

            }

        });


    }

    function fooevents_save_booking_options() {

        var options = jQuery('#fooevents_bookings_options_serialized').val();
        var bookings_post_id = jQuery('#fooevents_bookings_post_id').val();
        var post_title = jQuery('input[name="post_title"]').val();
        var WooCommerceEventsZoomTopic = jQuery('input[name="WooCommerceEventsZoomTopic"]').val();
        var nonce_val = jQuery('input[name=fooevents_bookings_options_nonce]').val();

        jQuery('input[name="WooCommerceEventsZoomTopic"]').val(post_title);

        var data = {
            'action': 'fooevents_save_booking_options',
            'options': options,
            'post_id': bookings_post_id,
            'post_title': post_title,
            'nonce_val': nonce_val,
            'WooCommerceEventsZoomTopic': WooCommerceEventsZoomTopic
        };

        jQuery.post(FooEventsBookingsProdObj.ajaxurl, data, function (response) {

            jQuery('#fooevents_bookings_save').text(FooEventsBookingsProdObj.saveChanges).removeAttr('disabled');

            var response_json = JSON.parse(response);

            if (response_json.update_zoom) {

                var booking_slot_ids = Object.keys(response_json);

                for (var booking_slot_index = 0; booking_slot_index < booking_slot_ids.length; booking_slot_index++) {

                    var booking_slot_id = booking_slot_ids[booking_slot_index];

                    if (booking_slot_id != 'update_zoom' && response_json[booking_slot_id].zoom_id == 'enabled') {

                        var booking_slot_option_keys = Object.keys(response_json[booking_slot_id]);

                        for (var booking_slot_option_index = 0; booking_slot_option_index < booking_slot_option_keys.length; booking_slot_option_index++) {

                            var booking_slot_option_key = booking_slot_option_keys[booking_slot_option_index];

                            if (booking_slot_option_key.indexOf('_zoom_id') > -1) {

                                var zoomID = response_json[booking_slot_id][booking_slot_option_key];
                                var booking_slot_option_key_parts = booking_slot_option_key.split("_");
                                var booking_slot_option_date_key = booking_slot_option_key_parts[0];
                                var zoomTopic = response_json[booking_slot_id][booking_slot_option_date_key + '_zoom_topic'];
                                var zoomSelect = jQuery('select#' + booking_slot_option_date_key + '_zoom_id');

                                if (zoomSelect.find('option[value="' + zoomID + '"]').length) {

                                    // Update existing meetings
                                    zoomSelect.find('option[value="' + zoomID + '"]').text(zoomTopic);

                                    jQuery('select#WooCommerceEventsZoomWebinar').find('option[value="' + zoomID + '"]').text(zoomTopic);

                                } else {

                                    // Add new meetings
                                    var endpointLabel = FooEventsBookingsProdObj.meetings;
                                    var zoomType = 2;

                                    if (zoomID.indexOf('_webinars') > -1) {

                                        endpointLabel = FooEventsBookingsProdObj.webinars;
                                        zoomType = 5;

                                    }

                                    zoomSelect.find('optgroup[label="' + endpointLabel + '"]').append('<option value="' + zoomID + '" data-zoom-type="' + zoomType + '">' + zoomTopic + '</option>');
                                    zoomSelect.val(zoomID).change();

                                    zoomSelect.parent().attr('data-zoom-id', zoomID);

                                    jQuery('select#WooCommerceEventsZoomWebinar optgroup[label="' + endpointLabel + '"]').append('<option value="' + zoomID + '" data-zoom-type="' + zoomType + '">' + zoomTopic + '</option>');

                                }

                                zoomSelect.select2();
                                jQuery('select#WooCommerceEventsZoomWebinar').select2();

                            }

                        }

                    }

                }

            }

            alert(FooEventsBookingsProdObj.optionsSaved);

        });

    }

    function fooevents_bookings_time_validation() {

        if (jQuery('.fooevents_bookings_time_hour').length) {

            jQuery('.fooevents_bookings_time_hour').each(function (i, obj) {

                var val = jQuery(obj).find(":selected").val();
                var parent = jQuery(obj).attr("id").split('_');

                if (parseInt(val) > 12) {

                    jQuery('#' + parent[0] + "_period").val('').prop('disabled', true);

                } else {

                    jQuery('#' + parent[0] + "_period").prop('disabled', false);

                }

            });

        }

    }

    function fooevents_bookings_time_validation_now(event) {

        var val = jQuery(event.target).find(":selected").val();
        var parent = jQuery(event.target).attr("id").split('_');

        if (parseInt(val) > 12) {

            jQuery('#' + parent[0] + "_period").val('').prop('disabled', true);

        } else {

            jQuery('#' + parent[0] + "_period").prop('disabled', false);

        }

    }

    function fooevents_bookings_insert_zoom_dropdowns() {

        if (jQuery('.fooevents_bookings_zoom_id_container').length && jQuery('select#WooCommerceEventsZoomWebinar').length) {

            jQuery('.fooevents_bookings_zoom_id_container').each(function () {
                var inputID = jQuery(this).attr('id').replace('_container', '');
                var currentVal = jQuery(this).attr('data-zoom-id');

                if (jQuery('select#' + inputID).length) {
                    currentVal = jQuery('select#' + inputID).val();
                }

                var zoomSelectClone = jQuery(
                    "select#WooCommerceEventsZoomWebinar"
                )
                    .clone()
                    .attr(
                        "class",
                        "fooevents_bookings_zoom_id_select fooevents-search-list"
                    ).data(
                        "bookings",
                        inputID
                    ).attr(
                        "id",
                        inputID
                    );

                zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.webinars + '"] option[data-zoom-type!="5"]').remove();

                if (zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.webinars + '"] option').length === 0) {
                    zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.webinars + '"]').remove();
                }

                zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.meetings + '"] option[data-zoom-type!="2"]').remove();

                if (zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.meetings + '"] option').length === 0) {
                    zoomSelectClone.find('optgroup[label="' + FooEventsBookingsProdObj.meetings + '"]').remove();
                }

                zoomSelectClone.val(currentVal);

                if (jQuery(this).attr('data-zoom-id-enabled') == '0') {
                    zoomSelectClone.addClass('bookings-exclude');
                }

                jQuery(this).html(zoomSelectClone);

                zoomSelectClone.select2();
            });

        }

    }

    fooevents_bookings_insert_zoom_dropdowns();

    function fooevents_bookings_delete_zoom(zoomID) {

        var nonce_val = jQuery('input[name=fooevents_bookings_options_nonce]').val();

        var data = {
            'action': 'fooevents_delete_zoom',
            'zoom_id': zoomID,
            'nonce_val': nonce_val
        };

        jQuery.post(FooEventsBookingsProdObj.ajaxurl, data, function (response) {

            var result = JSON.parse(response);

            if (result.status == 'success') {

                alert(FooEventsBookingsProdObj.zoomDeleteSuccess);

                jQuery('select#WooCommerceEventsZoomWebinar').find('option[value="' + zoomID + '"]').remove();

            } else {

                alert(FooEventsBookingsProdObj.zoomDeleteError);

            }

            fooevents_bookings_insert_zoom_dropdowns();

        });

    }

    if (jQuery('input[name="post_title"]').length) {

        jQuery('input[name="post_title"]').change(function () {

            jQuery('input#fooevents_bookings_zoom_topic').val(jQuery(this).val());

        });

    }

})(jQuery);