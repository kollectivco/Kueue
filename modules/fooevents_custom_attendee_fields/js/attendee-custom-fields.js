(function ($) {

    var typing_timer;
    var done_typing_interval = 800;


    jQuery('#fooevents_custom_attendee_fields_new_field').on('click', function () {

        fooevents_new_attendee_field();

        jQuery('#fooevents_custom_attendee_fields_info .fooevents_notfound').hide();

        return false;

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('click', '.fooevents_custom_attendee_fields_remove', function (event) {

        fooevents_delete_attendee_field(jQuery(this));

        return false;

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('keyup', '.fooevents_custom_attendee_fields_label', function (event) {

        clearTimeout(typing_timer);

        typing_timer = setTimeout(fooevents_update_attendee_row_ids, done_typing_interval, jQuery(this));

        return false;

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('keyup', '.fooevents_custom_attendee_fields_options', function (event) {

        fooevents_clean_options(jQuery(this));
        fooevents_serialize_options();

        return false;

    });


    jQuery('#fooevents_custom_attendee_fields_options_table').on('keydown', '.fooevents_custom_attendee_fields_label', function (event) {

        clearTimeout(typing_timer);

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('change', '.fooevents_custom_attendee_fields_req', function (event) {

        fooevents_serialize_options();

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('change', '.fooevents_custom_attendee_fields_type', function (event) {

        fooevents_serialize_options();
        fooevents_enable_disable_options(jQuery(this));

    });

    jQuery('#fooevents_custom_attendee_fields_options_table').on('change', '.fooevents_custom_attendee_fields_def', function (event) {

        fooevents_serialize_options();
        fooevents_enable_disable_options(jQuery(this));

    });

    fooevents_serialize_options();

    jQuery('table#fooevents_custom_attendee_fields_options_table tbody').sortable({

        update: function () {
            fooevents_reorder_rows();
        }

    });

    function fooevents_reorder_rows() {

        /*var data={};
        var item_num = 1;
        jQuery('#fooevents_custom_attendee_fields_options_table').find('tr').each(function(){
            var id=jQuery(this).attr('id');
            if(id) {
    
                jQuery(this).attr('id', item_num+"_option");
                jQuery(this).find('.fooevents_custom_attendee_fields_label').each(function(){
                    jQuery(this).attr('id', item_num+"_label");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_label').each(function(){
                    jQuery(this).attr('id', item_num+"_label");
                    jQuery(this).attr('name', item_num+"_label");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_type').each(function(){
                    jQuery(this).attr('id', item_num+"_type");
                    jQuery(this).attr('name', item_num+"_type");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_options').each(function(){
                    jQuery(this).attr('id', item_num+"_options");
                    jQuery(this).attr('name', item_num+"_options");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_def').each(function(){
                    jQuery(this).attr('id', item_num+"_def");
                    jQuery(this).attr('name', item_num+"_def");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_req').each(function(){
                    jQuery(this).attr('id', item_num+"_req");
                    jQuery(this).attr('name', item_num+"_req");
                });
    
                jQuery(this).find('.fooevents_custom_attendee_fields_remove').each(function(){
                    jQuery(this).attr('id', item_num+"_remove");
                    jQuery(this).attr('name', item_num+"_remove");
                });
    
                item_num++;
            }
    
        });*/

        fooevents_serialize_options();

    }

    function fooevents_new_attendee_field() {

        var opt_num = jQuery('#fooevents_custom_attendee_fields_options_table tr').length;
        var field_id = fooevents_custom_attendees_make_id(20);

        var sort = '<span class="dashicons dashicons-menu"></span>';
        var label = '<input type="text" id="' + field_id + '_label" name="' + field_id + '_label" class="fooevents_custom_attendee_fields_label" value="Label_' + opt_num + '" autocomplete="off" maxlength="150" />';
        var type = '<select id="' + field_id + '_type" name="' + field_id + '_type" class="fooevents_custom_attendee_fields_type"><option value="text">Text</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="checkbox">Checkbox</option><option value="radio">Radio</option><option value="country">Country</option><option value="date">Date</option><option value="time">Time</option><option value="email">Email</option><option value="url">URL</option><option value="numbers">Numbers</option><option value="alphabet">Alphabet</option><option value="alphanumeric">Alphanumeric</option></select>';
        var options = '<input id="' + field_id + '_options" name="' + field_id + '_options" class="fooevents_custom_attendee_fields_options" type="text" disabled autocomplete="off" />';
        var def = '<input id="' + field_id + '_def" name="' + field_id + '_def" type="text" class="fooevents_custom_attendee_fields_def" disabled autocomplete="off" />';
        var req = '<select id="' + field_id + '_req" name="' + field_id + '_req" class="fooevents_custom_attendee_fields_req"><option value="true">Yes</option><option value="false">No</option></select>';
        var remove = '<a href="#" id="' + field_id + '_remove" name="' + field_id + '_remove" class="fooevents_custom_attendee_fields_remove" class="fooevents_custom_attendee_fields_remove">[X]</a>';

        var new_field = '<tr id="' + field_id + '" class="fooevents_custom_attendee_fields_option"><td>' + sort + '</td><td>' + label + '</td><td>' + type + '</td><td>' + options + '</td><td>' + def + '</td><td>' + req + '</td><td>' + remove + '</td></tr>';
        jQuery('#fooevents_custom_attendee_fields_options_table tbody').append(new_field);

        fooevents_serialize_options();

    }

    function fooevents_delete_attendee_field(row) {

        row.closest('tr').remove();
        fooevents_reorder_rows();

    }

    function fooevents_change_attendee_field_type(row) {

        row.closest('.fooevents_custom_attendee_fields_options').remove();

    }

    function fooevents_update_attendee_row_ids(row) {

        /*var row_num = row.closest('tr').index()+1;
        var value = fooevents_encode_input(row.val());

        var new_label_id = row_num+'_label';
        var new_type_id = row_num+'_type';
        var new_options_id = row_num+'_options';
        var new_req_id = row_num+'_req';
        var new_remove_id = row_num+'_remove';
        var new_option_id = row_num+'_option';
        var new_def_id = row_num+'_def';

        fooevents_check_if_label_exists(value);

        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_label').attr("id", new_label_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_label').attr("name", new_label_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_type').attr("id", new_type_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_type').attr("name", new_type_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_options').attr("id", new_options_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_options').attr("name", new_options_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_req').attr("id", new_req_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_req').attr("name", new_req_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_def').attr("id", new_def_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_def').attr("name", new_def_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_remove').attr("id", new_remove_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+') .fooevents_custom_attendee_fields_remove').attr("name", new_remove_id);
        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq('+row_num+')').attr("id", new_option_id);
        */
        fooevents_serialize_options();

    }

    function fooevents_encode_input(input) {

        var output = input.toLowerCase();
        output = output.replace(/ /g, "_");

        return output;

    }

    function fooevents_get_row_option_names() {

        var IDs = [];
        jQuery("#fooevents_custom_attendee_fields_options_table").find("tr").each(function () { IDs.push(this.id); });

        return IDs;

    }

    function fooevents_check_if_label_exists(value) {

        var arr = [];
        jQuery(".fooevents_custom_attendee_fields_label").each(function () {
            var value = jQuery(this).val();
            if (arr.indexOf(value) == -1)
                arr.push(value);
            else
                alert('Label is already in use');
        });

    }

    function fooevents_serialize_options() {

        var data = {};
        var item_num = 0;
        jQuery('#fooevents_custom_attendee_fields_options_table').find('tr').each(function () {
            var id = jQuery(this).attr('id');
            if (item_num) {
                var row = {};
                jQuery(this).find('input,select,textarea').each(function () {
                    row[jQuery(this).attr('name')] = jQuery(this).val();
                });
                data[id] = row;
            }

            item_num++;
        });

        data = JSON.stringify(data);

        jQuery('#fooevents_custom_attendee_fields_options_serialized').val(data);


    }

    function fooevents_enable_disable_options(row) {

        var row_num = row.closest('tr').index() + 1;
        var option_type = jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_type').val();

        if (option_type == 'select' || option_type == 'radio') {

            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_options').prop("disabled", false);
            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_def').prop("disabled", false);

        } else {

            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_options').prop("disabled", true);
            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_options').val("");

            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_def').prop("disabled", true);
            jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_def').val("");

        }

        fooevents_serialize_options();

    }

    function fooevents_clean_options(row) {

        console.log(row);

        var row_num = row.closest('tr').index() + 1;
        var value = jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_options').val();

        var cleaned = value.replace(/\s*\|\s*/g, "|");

        jQuery('#fooevents_custom_attendee_fields_options_table tr:eq(' + row_num + ') .fooevents_custom_attendee_fields_options').val(cleaned);

    }

    function fooevents_custom_attendees_make_id(length) {

        var result = '';
        var characters = 'abcdefghijklmnopqrstuvwxyz';
        var charactersLength = characters.length;

        for (var i = 0; i < length; i++) {

            result += characters.charAt(Math.floor(Math.random() * charactersLength));

        }

        return result;

    }

})(jQuery);