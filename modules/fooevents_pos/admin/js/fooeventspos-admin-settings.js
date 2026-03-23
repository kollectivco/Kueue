jQuery(document).ready(function () {
  if (jQuery("#fooeventspos_save_license_key_button").length > 0) {
    jQuery("#fooeventspos_save_license_key_button").click(function () {
      var saveButton = jQuery(this);

      saveButton
        .attr("value", fooeventsposScriptObj.buttonSaving)
        .prop("disabled", true)
        .addClass("disabled")
        .after(
          '<img src="' +
            fooeventsposScriptObj.adminURL +
            'images/loading.gif" class="fooeventspos-ajax-spinner" />'
        );

      var apiKey = jQuery.trim(jQuery("#globalWooCommerceEventsAPIKey").val());

      var data = {
        action: "fooeventspos_save_license_key",
        globalWooCommerceEventsAPIKey: apiKey,
      };

      jQuery.post(ajaxurl, data, function () {
        saveButton.attr("value", fooeventsposScriptObj.buttonSaved);

        setTimeout(function () {
          saveButton
            .attr("value", fooeventsposScriptObj.buttonSave)
            .removeClass("disabled")
            .prop("disabled", false);
        }, 2000);

        jQuery(".fooeventspos-ajax-spinner").remove();
      });
    });
  }

  if (jQuery("#globalFooEventsPOSUseCheckinsSettings").length > 0) {
    jQuery("#globalFooEventsPOSUseCheckinsSettings").change(function () {
      if (jQuery(this).prop("checked") === true) {
        jQuery("tr.fooeventspos-hideable-row").hide();
      } else {
        jQuery("tr.fooeventspos-hideable-row").css("display", "table-row");
      }
    });
  }
});
