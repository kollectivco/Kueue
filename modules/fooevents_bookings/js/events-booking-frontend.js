(function ($) {
  jQuery("body").on("added_to_cart", function (event) {
    fooevents_bookings_reset_date_slot();
  });

  if (jQuery(".fooevents-bookings-slot").length) {
    jQuery(".fooevents-bookings-slot").on("change", function (event) {
      var slot_element = jQuery(this);
      fooevents_bookings_return_slot_dates(slot_element, event);
    });
  }

  if (jQuery(".fooevents-bookings-date").length) {
    jQuery(".fooevents-bookings-date").on("change", function (event) {
      var date_element = jQuery(this);
      fooevents_bookings_return_date_stock(date_element, event);
    });
  }

  if (jQuery(".fooevents-bookings-slot-date").length) {
    jQuery(".fooevents-bookings-slot-date").on("change", function (event) {
      var slot_date_element = jQuery(this);
      fooevents_bookings_return_slot_date_stock(slot_date_element, event);
    });
  }

  if (jQuery(".fooevents-bookings-date-slot-date").length) {
    jQuery(".fooevents-bookings-date-slot-date").on("change", function (event) {
      var date_slot_date_element = jQuery(this);
      fooevents_bookings_return_date_slots(date_slot_date_element, event);
    });
  }

  if (jQuery(".fooevents-bookings-date-slot-slot").length) {
    jQuery(".fooevents-bookings-date-slot-slot").on("change", function (event) {
      var slot_element = jQuery(this);
      fooevents_bookings_return_date_slot_stock(slot_element, event);
    });
  }

  if (jQuery(".fooevents_calendar_selected").length) {
    if (jQuery(".fooevents-bookings-date").length) {
      var date_element = jQuery(".fooevents-bookings-date");
      fooevents_bookings_return_date_stock(date_element, event);
    }

    if (jQuery(".fooevents-bookings-slot-date").length) {
      var slot_date_element = jQuery(".fooevents-bookings-slot-date");
      fooevents_bookings_return_slot_date_stock(slot_date_element, event);
    }

    if (jQuery(".fooevents-bookings-date-slot-slot").length) {
      var slot_element = jQuery(".fooevents-bookings-date-slot-slot");
      fooevents_bookings_return_date_slot_stock(slot_element, event);
    }
  }

  function fooevents_bookings_return_slot_dates(slot_element, event) {
    var parent_result = jQuery(slot_element).attr("id").split("_");
    var date_element =
      "#fooevents_bookings_date_" + parent_result[3] + "__" + parent_result[5];
    var selected = slot_element.find(":selected").val();

    jQuery(date_element)
      .children()
      .remove()
      .end()
      .append(
        '<option value="">' + FooEventsBookingsFrontObj.loading + "</option>"
      );

    var data_slots = {
      action: "fetch_fooevents_bookings_dates",
      selected: selected,
    };

    jQuery.post(
      FooEventsBookingsFrontObj.ajaxurl,
      data_slots,
      function (response) {
        var slots = JSON.parse(response);
        var date_default = jQuery(date_element).attr("data-placeholder");

        jQuery(date_element)
          .find("option")
          .remove()
          .end()
          .append('<option value="">' + date_default + "</option>")
          .val("whatever");

        jQuery.each(slots, function (index, value) {
          jQuery(date_element).append(
            jQuery("<option>", {
              value: index,
              text: value,
            })
          );
        });
      }
    );
  }

  function fooevents_bookings_return_date_slots(date_slot_date_element, event) {
    var parent_result = jQuery(date_slot_date_element).attr("id").split("_");

    var slot_element = "";
    if (jQuery("#fooevents_bookings_slot_val__trans").length) {
      slot_element = "#fooevents_bookings_slot_val__trans";
    } else {
      slot_element =
        "#fooevents_bookings_date_slot_slot_" +
        parent_result[5] +
        "__" +
        parent_result[7];
    }

    var selected = date_slot_date_element.find(":selected").val();

    jQuery(slot_element)
      .children()
      .remove()
      .end()
      .append(
        '<option value="">' + FooEventsBookingsFrontObj.loading + "</option>"
      );

    var data_slots = {
      action: "fetch_fooevents_bookings_date_slot_slots",
      selected: selected,
    };

    jQuery.post(
      FooEventsBookingsFrontObj.ajaxurl,
      data_slots,
      function (response) {
        var slots = JSON.parse(response);
        var slot_default = jQuery(slot_element).attr("data-placeholder");

        jQuery(slot_element)
          .find("option")
          .remove()
          .end()
          .append('<option value="">' + slot_default + "</option>")
          .val("whatever");

        jQuery.each(slots, function (index, value) {
          jQuery(slot_element).append(
            jQuery("<option>", {
              value: value.slot_id + "_" + value.date_id + "_" + value.product,
              text: value.slot_label,
            })
          );
        });
      }
    );
  }

  function fooevents_bookings_return_date_stock(date_element, event) {
    var parent_result = jQuery(date_element).attr("id").split("_");
    var date_selected = date_element.find(":selected").val();

    var slot_element =
      "#fooevents_bookings_slot_" + parent_result[3] + "__" + parent_result[5];
    var slot_selected = jQuery(slot_element).find(":selected").val();

    if (typeof slot_selected === "undefined") {
      slot_selected = jQuery(slot_element).val();
    }

    var info_element =
      "#fooevents-checkout-attendee-info-" +
      parent_result[3] +
      "-" +
      parent_result[5];

    var data_date_stock = {
      action: "fetch_fooevents_bookings_date_stock",
      slot_selected: slot_selected,
      date_selected: date_selected,
    };

    jQuery.post(
      FooEventsBookingsFrontObj.ajaxurl,
      data_date_stock,
      function (response) {
        var returned_stock = JSON.parse(response);
        jQuery(info_element).html(returned_stock.stock);

        jQuery("#fooevents-bookings-stock").val(returned_stock.stock_val);
        jQuery(".qty").attr("max", returned_stock.stock_val);
        if (returned_stock.stock_val == 0) {
          jQuery(".single_add_to_cart_button").attr("disabled", true);
        } else {
          jQuery(".single_add_to_cart_button").attr("disabled", false);
        }
      }
    );
  }

  function fooevents_bookings_return_slot_date_stock(slot_date_element, event) {
    var parent_result = jQuery(slot_date_element).attr("id").split("_");
    var slot_date_selected = slot_date_element.find(":selected").val();

    var info_element =
      "#fooevents-checkout-attendee-info-" +
      parent_result[4] +
      "-" +
      parent_result[6];

    var data_slot_date_stock = {
      action: "fetch_fooevents_bookings_slot_date_stock",
      slot_date_selected: slot_date_selected,
    };

    jQuery.post(
      FooEventsBookingsFrontObj.ajaxurl,
      data_slot_date_stock,
      function (response) {
        var returned_stock = JSON.parse(response);
        jQuery(info_element).html(returned_stock.stock);

        jQuery("#fooevents-bookings-stock").val(returned_stock.stock_val);
        jQuery(".qty").attr("max", returned_stock.stock_val);
        if (returned_stock.stock_val == 0) {
          jQuery(".single_add_to_cart_button").attr("disabled", true);
        } else {
          jQuery(".single_add_to_cart_button").attr("disabled", false);
        }
      }
    );
  }

  function fooevents_bookings_return_date_slot_stock(slot_element, event) {
    var parent_result = jQuery(slot_element).attr("id").split("_");
    var date_slot_slot_selected = slot_element.find(":selected").val();

    var date_element = "";
    if (jQuery("#fooevents_bookings_date_val__trans").length) {
      date_element = "#fooevents_bookings_date_val__trans";
    } else {
      date_element =
        "#fooevents_bookings_date_slot_date_" +
        parent_result[5] +
        "__" +
        parent_result[7];
    }

    var date_slot_date_selected = jQuery(date_element).find(":selected").val();

    var info_element =
      "#fooevents-checkout-attendee-info-" +
      parent_result[5] +
      "-" +
      parent_result[7];
    if (!jQuery(info_element).length) {
      info_element = ".fooevents-checkout-attendee-info";
    }

    var data_date_slot_stock = {
      action: "fetch_fooevents_bookings_date_slot_stock",
      date_slot_slot_selected: date_slot_slot_selected,
      date_slot_date_selected: date_slot_date_selected,
    };

    if (date_slot_slot_selected && date_slot_date_selected) {
      jQuery.post(
        FooEventsBookingsFrontObj.ajaxurl,
        data_date_slot_stock,
        function (response) {
          var returned_stock = JSON.parse(response);
          jQuery(info_element).html(returned_stock.stock);

          jQuery("#fooevents-bookings-stock").val(returned_stock.stock_val);
          jQuery(".qty").attr("max", returned_stock.stock_val);

          if (returned_stock.stock_val == 0) {
            jQuery(".single_add_to_cart_button").attr("disabled", true);
          } else {
            jQuery(".single_add_to_cart_button").attr("disabled", false);
          }
        }
      );
    } else {
      jQuery(info_element).html("");
    }
  }

  function fooevents_bookings_reset_date_slot() {
    jQuery("#fooevents_bookings_date_val__trans option").each(function () {
      jQuery(this).remove();
      jQuery("#fooevents-checkout-attendee-info-val-trans").html("");
    });
    var date_default = jQuery("#fooevents_bookings_date_val__trans").attr(
      "data-placeholder"
    );
    jQuery("#fooevents_bookings_date_val__trans").append(
      '<option value="">' + date_default + "</option>"
    );
    jQuery("#fooevents_bookings_slot_val__trans option:eq(0)").attr(
      "selected",
      "selected"
    );
    jQuery("#fooevents_bookings_date_val__trans option:eq(0)").attr(
      "selected",
      "selected"
    );
    jQuery("#fooevents_bookings_slot_date_val_trans option:eq(0)").attr(
      "selected",
      "selected"
    );
  }
})(jQuery);
