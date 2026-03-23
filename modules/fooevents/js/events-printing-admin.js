(function ($) {
  jQuery("#WooCommercePrintTicketSize").on("change", function () {
    fooevents_set_number_columns_rows(jQuery(this).find(":selected").val());
  });

  jQuery("#WooCommercePrintTicketNrColumns").on("change", function () {
    fooevents_set_layout_columns(jQuery(this).val());

    jQuery("#fooevents_printing_layout_block .fooevents_printing_widget").each(
      function () {
        jQuery(this).css("width", "");
      }
    );

    fooevents_set_layout_widget_resized_width();
  });

  jQuery("#WooCommercePrintTicketNrRows").on("change", function () {
    fooevents_set_layout_rows(jQuery(this).val());
  });

  jQuery("#WooCommercePrintTicketNumbers").on("keyup", function () {
    fooevents_enable_print_all_tickets_checkbox();
  });

  jQuery("#WooCommercePrintTicketOrders").on("keyup", function () {
    fooevents_enable_print_all_tickets_checkbox();
  });

  jQuery("#WooCommerceEventsPrintAllTickets").on("change", function () {
    fooevents_check_print_all_tickets_button(jQuery(this));
  });

  jQuery(".custom_tab_fooevents_printing").on("click", function () {
    fooevents_set_layout_widget_resized_width();

    jQuery("#fooevents_printing_widgets").accordion(
      {
        animate: true,
      },
      {
        collapsible: true,
        heightStyle: "content",
      }
    );
  });

  jQuery("#fooevents-add-printing-widgets").on("click", function () {
    fooevents_expand_collapse_fields();
  });

  jQuery(
    "#fooevents_printing_layout_block .fooevents_printing_widget > span"
  ).on("click", function () {
    fooevents_show_hide_widget_details(jQuery(this));
  });

  if (jQuery("#WooCommerceEventsProductIsEvent").length) {
    if (typeof localObjPrint === "object" && localObjPrint !== null) {
      jQuery("#fooevents_printing_save").on("click", function (e) {
        e.preventDefault();
        fooevents_save_printing_options();
        return false;
      });
    }

    function fooevents_save_printing_options() {
      jQuery("#fooevents_printing_save").prop("disabled", true);

      var data = {
        action: "fooevents_save_printing_options",
        post_id: jQuery("#post_ID").val(),
        WooCommercePrintTicketSize: jQuery("#WooCommercePrintTicketSize").val(),
        WooCommercePrintTicketNrColumns: jQuery(
          "#WooCommercePrintTicketNrColumns"
        ).val(),
        WooCommercePrintTicketNrRows: jQuery(
          "#WooCommercePrintTicketNrRows"
        ).val(),
        WooCommerceBadgeFieldTopLeft: jQuery(
          "#WooCommerceBadgeFieldTopLeft"
        ).val(),
        WooCommerceBadgeFieldTopMiddle: jQuery(
          "#WooCommerceBadgeFieldTopMiddle"
        ).val(),
        WooCommerceBadgeFieldTopRight: jQuery(
          "#WooCommerceBadgeFieldTopRight"
        ).val(),
        WooCommerceBadgeField_a_4: jQuery("#WooCommerceBadgeField_a_4").val(),
        WooCommerceBadgeFieldMiddleLeft: jQuery(
          "#WooCommerceBadgeFieldMiddleLeft"
        ).val(),
        WooCommerceBadgeFieldMiddleMiddle: jQuery(
          "#WooCommerceBadgeFieldMiddleMiddle"
        ).val(),
        WooCommerceBadgeFieldMiddleRight: jQuery(
          "#WooCommerceBadgeFieldMiddleRight"
        ).val(),
        WooCommerceBadgeField_b_4: jQuery("#WooCommerceBadgeField_b_4").val(),
        WooCommerceBadgeFieldBottomLeft: jQuery(
          "#WooCommerceBadgeFieldBottomLeft"
        ).val(),
        WooCommerceBadgeFieldBottomMiddle: jQuery(
          "#WooCommerceBadgeFieldBottomMiddle"
        ).val(),
        WooCommerceBadgeFieldBottomRight: jQuery(
          "#WooCommerceBadgeFieldBottomRight"
        ).val(),
        WooCommerceBadgeField_c_4: jQuery("#WooCommerceBadgeField_c_4").val(),
        WooCommerceBadgeField_d_1: jQuery("#WooCommerceBadgeField_d_1").val(),
        WooCommerceBadgeField_d_2: jQuery("#WooCommerceBadgeField_d_2").val(),
        WooCommerceBadgeField_d_3: jQuery("#WooCommerceBadgeField_d_3").val(),
        WooCommerceBadgeField_d_4: jQuery("#WooCommerceBadgeField_d_4").val(),
        WooCommerceBadgeFieldTopLeft_font: jQuery(
          "#WooCommerceBadgeFieldTopLeft_font"
        ).val(),
        WooCommerceBadgeFieldTopMiddle_font: jQuery(
          "#WooCommerceBadgeFieldTopMiddle_font"
        ).val(),
        WooCommerceBadgeFieldTopRight_font: jQuery(
          "#WooCommerceBadgeFieldTopRight_font"
        ).val(),
        WooCommerceBadgeField_a_4_font: jQuery(
          "#WooCommerceBadgeField_a_4_font"
        ).val(),
        WooCommerceBadgeFieldMiddleLeft_font: jQuery(
          "#WooCommerceBadgeFieldMiddleLeft_font"
        ).val(),
        WooCommerceBadgeFieldMiddleMiddle_font: jQuery(
          "#WooCommerceBadgeFieldMiddleMiddle_font"
        ).val(),
        WooCommerceBadgeFieldMiddleRight_font: jQuery(
          "#WooCommerceBadgeFieldMiddleRight_font"
        ).val(),
        WooCommerceBadgeField_b_4_font: jQuery(
          "#WooCommerceBadgeField_b_4_font"
        ).val(),
        WooCommerceBadgeFieldBottomLeft_font: jQuery(
          "#WooCommerceBadgeFieldBottomLeft_font"
        ).val(),
        WooCommerceBadgeFieldBottomMiddle_font: jQuery(
          "#WooCommerceBadgeFieldBottomMiddle_font"
        ).val(),
        WooCommerceBadgeFieldBottomRight_font: jQuery(
          "#WooCommerceBadgeFieldBottomRight_font"
        ).val(),
        WooCommerceBadgeField_c_4_font: jQuery(
          "#WooCommerceBadgeField_c_4_font"
        ).val(),
        WooCommerceBadgeField_d_1_font: jQuery(
          "#WooCommerceBadgeField_d_1_font"
        ).val(),
        WooCommerceBadgeField_d_2_font: jQuery(
          "#WooCommerceBadgeField_d_2_font"
        ).val(),
        WooCommerceBadgeField_d_3_font: jQuery(
          "#WooCommerceBadgeField_d_3_font"
        ).val(),
        WooCommerceBadgeField_d_4_font: jQuery(
          "#WooCommerceBadgeField_d_4_font"
        ).val(),
        WooCommerceBadgeFieldTopLeft_logo: jQuery(
          "#WooCommerceBadgeFieldTopLeft_logo"
        ).val(),
        WooCommerceBadgeFieldTopMiddle_logo: jQuery(
          "#WooCommerceBadgeFieldTopMiddle_logo"
        ).val(),
        WooCommerceBadgeFieldTopRight_logo: jQuery(
          "#WooCommerceBadgeFieldTopRight_logo"
        ).val(),
        WooCommerceBadgeField_a_4_logo: jQuery(
          "#WooCommerceBadgeField_a_4_logo"
        ).val(),
        WooCommerceBadgeFieldMiddleLeft_logo: jQuery(
          "#WooCommerceBadgeFieldMiddleLeft_logo"
        ).val(),
        WooCommerceBadgeFieldMiddleMiddle_logo: jQuery(
          "#WooCommerceBadgeFieldMiddleMiddle_logo"
        ).val(),
        WooCommerceBadgeFieldMiddleRight_logo: jQuery(
          "#WooCommerceBadgeFieldMiddleRight_logo"
        ).val(),
        WooCommerceBadgeField_b_4_logo: jQuery(
          "#WooCommerceBadgeField_b_4_logo"
        ).val(),
        WooCommerceBadgeFieldBottomLeft_logo: jQuery(
          "#WooCommerceBadgeFieldBottomLeft_logo"
        ).val(),
        WooCommerceBadgeFieldBottomMiddle_logo: jQuery(
          "#WooCommerceBadgeFieldBottomMiddle_logo"
        ).val(),
        WooCommerceBadgeFieldBottomRight_logo: jQuery(
          "#WooCommerceBadgeFieldBottomRight_logo"
        ).val(),
        WooCommerceBadgeField_c_4_logo: jQuery(
          "#WooCommerceBadgeFieldBottomRight_logo"
        ).val(),
        WooCommerceBadgeField_d_1_logo: jQuery(
          "#WooCommerceBadgeField_d_1_logo"
        ).val(),
        WooCommerceBadgeField_d_2_logo: jQuery(
          "#WooCommerceBadgeField_d_2_logo"
        ).val(),
        WooCommerceBadgeField_d_3_logo: jQuery(
          "#WooCommerceBadgeField_d_3_logo"
        ).val(),
        WooCommerceBadgeField_d_4_logo: jQuery(
          "#WooCommerceBadgeField_d_4_logo"
        ).val(),
        WooCommerceBadgeFieldTopLeft_custom:
          tinymce.get("WooCommerceBadgeFieldTopLeft_custom") !== null
            ? tinymce.get("WooCommerceBadgeFieldTopLeft_custom").getContent()
            : "",
        WooCommerceBadgeFieldTopMiddle_custom:
          tinymce.get("WooCommerceBadgeFieldTopMiddle_custom") !== null
            ? tinymce.get("WooCommerceBadgeFieldTopMiddle_custom").getContent()
            : "",
        WooCommerceBadgeFieldTopRight_custom:
          tinymce.get("WooCommerceBadgeFieldTopRight_custom") !== null
            ? tinymce.get("WooCommerceBadgeFieldTopRight_custom").getContent()
            : "",
        WooCommerceBadgeField_a_4_custom:
          tinymce.get("WooCommerceBadgeField_a_4_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_a_4_custom").getContent()
            : "",
        WooCommerceBadgeFieldMiddleLeft_custom:
          tinymce.get("WooCommerceBadgeFieldMiddleLeft_custom") !== null
            ? tinymce.get("WooCommerceBadgeFieldMiddleLeft_custom").getContent()
            : "",
        WooCommerceBadgeFieldMiddleMiddle_custom:
          tinymce.get("WooCommerceBadgeFieldMiddleMiddle_custom") !== null
            ? tinymce
                .get("WooCommerceBadgeFieldMiddleMiddle_custom")
                .getContent()
            : "",
        WooCommerceBadgeFieldMiddleRight_custom:
          tinymce.get("WooCommerceBadgeFieldMiddleRight_custom") !== null
            ? tinymce
                .get("WooCommerceBadgeFieldMiddleRight_custom")
                .getContent()
            : "",
        WooCommerceBadgeField_b_4_custom:
          tinymce.get("WooCommerceBadgeField_b_4_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_b_4_custom").getContent()
            : "",
        WooCommerceBadgeFieldBottomLeft_custom:
          tinymce.get("WooCommerceBadgeFieldBottomLeft_custom") !== null
            ? tinymce.get("WooCommerceBadgeFieldBottomLeft_custom").getContent()
            : "",
        WooCommerceBadgeFieldBottomMiddle_custom:
          tinymce.get("WooCommerceBadgeFieldBottomMiddle_custom") !== null
            ? tinymce
                .get("WooCommerceBadgeFieldBottomMiddle_custom")
                .getContent()
            : "",
        WooCommerceBadgeFieldBottomRight_custom:
          tinymce.get("WooCommerceBadgeFieldBottomRight_custom") !== null
            ? tinymce
                .get("WooCommerceBadgeFieldBottomRight_custom")
                .getContent()
            : "",
        WooCommerceBadgeField_c_4_custom:
          tinymce.get("WooCommerceBadgeField_c_4_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_c_4_custom").getContent()
            : "",
        WooCommerceBadgeField_d_1_custom:
          tinymce.get("WooCommerceBadgeField_d_1_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_d_1_custom").getContent()
            : "",
        WooCommerceBadgeField_d_2_custom:
          tinymce.get("WooCommerceBadgeField_d_2_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_d_2_custom").getContent()
            : "",
        WooCommerceBadgeField_d_3_custom:
          tinymce.get("WooCommerceBadgeField_d_3_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_d_3_custom").getContent()
            : "",
        WooCommerceBadgeField_d_4_custom:
          tinymce.get("WooCommerceBadgeField_d_4_custom") !== null
            ? tinymce.get("WooCommerceBadgeField_d_4_custom").getContent()
            : "",
        WooCommercePrintTicketSort: jQuery("#WooCommercePrintTicketSort").val(),
        WooCommercePrintTicketNumbers: jQuery(
          "#WooCommercePrintTicketNumbers"
        ).val(),
        WooCommercePrintTicketOrders: jQuery(
          "#WooCommercePrintTicketOrders"
        ).val(),
        WooCommerceEventsTicketBackgroundImage: jQuery(
          "#WooCommerceEventsTicketBackgroundImage"
        ).val(),
        WooCommerceEventsCutLinesPrintTicket: jQuery(
          "#WooCommerceEventsCutLinesPrintTicket"
        ).is(":checked")
          ? "on"
          : "off",
      };

      jQuery.post(ajaxurl, data, function (response) {
        jQuery("#fooevents_printing_save").prop("disabled", false);

        var status = JSON.parse(response);

        if (status.status == "success") {
          alert(localObjPrint.ajaxSaveSuccess);
        } else {
          alert(localObjPrint.ajaxSaveError);
        }
      });
    }
  }

  function fooevents_enable_print_all_tickets_checkbox() {
    if (
      jQuery("#WooCommercePrintTicketNumbers").val() == "" &&
      jQuery("#WooCommercePrintTicketOrders").val() == ""
    ) {
      jQuery("#WooCommerceEventsPrintAllTickets").prop("checked", true);
    } else {
      jQuery("#WooCommerceEventsPrintAllTickets").prop("checked", false);
    }

    jQuery("#WooCommerceEventsPrintAllTickets").change();
  }

  function fooevents_expand_collapse_fields() {
    if (jQuery("#fooevents_printing_widgets").is(":visible")) {
      jQuery("#fooevents_printing_widgets").slideUp();
      jQuery("#fooevents-add-printing-widgets").html("+ Expand Fields");
    } else {
      jQuery("#fooevents_printing_widgets").slideDown();
      fooevents_set_init_widget_resized_width();
      jQuery("#fooevents-add-printing-widgets").html("- Hide Fields");
    }
  }

  function fooevents_show_hide_widget_details(printingWidget) {
    if (printingWidget.next().css("display") == "block") {
      printingWidget
        .find(".fooevents_printing_arrow")
        .addClass("fooevents_printing_arrow_closed");
      printingWidget
        .find(".fooevents_printing_arrow")
        .removeClass("fooevents_printing_arrow_open");
    } else {
      printingWidget
        .find(".fooevents_printing_arrow")
        .addClass("fooevents_printing_arrow_open");
      printingWidget
        .find(".fooevents_printing_arrow")
        .removeClass("fooevents_printing_arrow_closed");
    }

    printingWidget.next().slideToggle();
  }

  function fooevents_remove_printing_widget() {
    jQuery(".fooevents_printing_widget_remove").off("click");

    jQuery(".fooevents_printing_widget_remove").on("click", function () {
      editor_id = jQuery(this).parent().find("textarea").attr("id");

      if (editor_id != "WooCommerceEventsPrintTicketCustom") {
        tinymce.EditorManager.execCommand("mceRemoveEditor", false, editor_id);
      }

      jQuery(this).parent().find("input.uploadfield").val("");

      var printSlotID =
        "#WooCommerceBadgeField" + jQuery(this).parents("td").attr("id");

      jQuery(printSlotID).val("");

      jQuery(this).parent().parent().remove();
    });
  }

  function fooevents_calculate_widget_resized_width() {
    var setWidth = jQuery("#fooevents_printing_layout_block").width();
    var nrCols = parseInt(jQuery("#WooCommercePrintTicketNrColumns").val());
    setWidth = setWidth / nrCols - 40;
    return setWidth;
  }

  function fooevents_set_layout_widget_resized_width() {
    jQuery("#fooevents_printing_layout_block .fooevents_printing_widget").each(
      function () {
        jQuery(this).css(
          "width",
          fooevents_calculate_widget_resized_width() + "px"
        );
      }
    );
  }

  function fooevents_set_init_widget_resized_width() {
    var setWidth = jQuery(".fooevents_printing_widget_init").width();

    jQuery(".fooevents_printing_widget_init").each(function () {
      jQuery(this).css("width", setWidth + "px");
    });
  }

  function fooevents_set_number_columns_rows(selectedSize) {
    switch (selectedSize) {
      case "tickets_avery_letter_10":
        jQuery("#WooCommercePrintTicketNrColumns").val("3").change();
        jQuery("#WooCommercePrintTicketNrRows").val("3").change();
        break;

      case "tickets_letter_10":
        jQuery("#WooCommercePrintTicketNrColumns").val("3").change();
        jQuery("#WooCommercePrintTicketNrRows").val("3").change();
        break;

      case "tickets_a4_10":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "tickets_a4_3":
        jQuery("#WooCommercePrintTicketNrColumns").val("3").change();
        jQuery("#WooCommercePrintTicketNrRows").val("3").change();
        break;

      case "letter_6":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "letter_10":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "a4_12":
        jQuery("#WooCommercePrintTicketNrColumns").val("1").change();
        jQuery("#WooCommercePrintTicketNrRows").val("3").change();
        break;

      case "a4_16":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("1").change();
        break;

      case "a4_24":
        jQuery("#WooCommercePrintTicketNrColumns").val("1").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "letter_30":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("1").change();
        break;

      case "a4_39":
        jQuery("#WooCommercePrintTicketNrColumns").val("2").change();
        jQuery("#WooCommercePrintTicketNrRows").val("1").change();
        break;

      case "a4_45":
        jQuery("#WooCommercePrintTicketNrColumns").val("1").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "dk2113_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("1").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "letter_labels_5":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "letter_labels_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "wristband_boca_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("2").change();
        break;

      case "letter_certificate_portrait_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("4").change();
        break;

      case "letter_certificate_landscape_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("4").change();
        break;

      case "a4_certificate_portrait_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("4").change();
        break;

      case "a4_certificate_landscape_1":
        jQuery("#WooCommercePrintTicketNrColumns").val("4").change();
        jQuery("#WooCommercePrintTicketNrRows").val("4").change();
        break;
    }
  }

  function fooevents_check_print_all_tickets_button(checkbox) {
    if (checkbox.prop("checked")) {
      jQuery("#WooCommercePrintTicketNumbers").val("").prop("disabled", true);
      jQuery("#WooCommercePrintTicketOrders").val("").prop("disabled", true);
      jQuery("#WooCommercePrintTicketSort").prop("disabled", false);
    } else {
      jQuery("#WooCommercePrintTicketNumbers").prop("disabled", false);
      jQuery("#WooCommercePrintTicketOrders").prop("disabled", false);
      jQuery("#WooCommercePrintTicketSort").prop("disabled", true);
    }
  }

  jQuery(window).resize(function () {
    jQuery("#fooevents_printing_layout_block .fooevents_printing_widget").each(
      function () {
        jQuery(this).css("width", "");
      }
    );

    fooevents_set_layout_widget_resized_width();
    fooevents_set_init_widget_resized_width();
  });

  fooevents_remove_printing_widget();
  fooevents_enable_print_all_tickets_checkbox();

  jQuery("#WooCommercePrintTicketNrColumns").ready(function () {
    fooevents_set_layout_columns(
      jQuery("#WooCommercePrintTicketNrColumns").val()
    );
  });

  jQuery("#WooCommercePrintTicketNrRows").ready(function () {
    fooevents_set_layout_rows(jQuery("#WooCommercePrintTicketNrRows").val());
  });

  jQuery(function () {
    var origSlot = "";

    jQuery(".fooevents_printing_widget").each(function () {
      var printingWidget = jQuery(this);
      var editor_id = printingWidget.find("textarea").attr("id");

      if (printingWidget.find("span").attr("data-name") == "custom") {
        tinymce.init({
          selector: "#" + jQuery(this).find("textarea").attr("id"),
          branding: false,
          elementpath: false,
          menubar: false,
          plugins: "lists",
          toolbar: [
            "bold italic bullist numlist alignleft aligncenter alignright",
          ],
        });
      }

      printingWidget.draggable({
        revert: "invalid",
        helper: "clone",
        zIndex: 1000,
        appendTo: "body",

        start: function (e, ui) {
          printingWidget
            .parent("#fooevents_printing_layout_block")
            .addClass("fooevents_printing_widget_layout_active");
          tinymce.EditorManager.execCommand(
            "mceRemoveEditor",
            false,
            editor_id
          );
          if (!printingWidget.hasClass("fooevents_printing_widget_init")) {
            origSlot =
              "#WooCommerceBadgeField" +
              printingWidget.parents("td").attr("id");
          } else {
            origSlot = "";
          }
        },

        stop: function (e, ui) {
          editor_id = printingWidget.find("textarea").attr("id");
          setTimeout(function () {
            tinymce.init({
              selector: "#" + editor_id,
              branding: false,
              elementpath: false,
              menubar: false,
              plugins: "lists",
              toolbar: [
                "bold italic bullist numlist alignleft aligncenter alignright",
              ],
            });
          }, 500);
        },
      });
    });

    var dropOption = {
      accept: ".fooevents_printing_widget",
      hoverClass: "fooevents_printing_widget_hover",
      activeClass: "fooevents_printing_widget_active",
      tolerance: "pointer",
      greedy: true,
      drop: function (event, ui) {
        jQuery(ui.helper).remove();
        fooevents_remove_printing_widget();

        if (
          jQuery(this).is(".fooevents_printing_slot") &&
          !jQuery(this).has(".fooevents_printing_widget").length
        ) {
          var slotID = "WooCommerceBadgeField" + jQuery(this).attr("id");
          var printSlotID = "#" + slotID;

          if (jQuery(ui.draggable).hasClass("fooevents_printing_widget_init")) {
            var clonedItem = jQuery(ui.draggable).clone();

            var clonedPosition = jQuery(ui.draggable).attr("data-order");

            clonedItem.insertBefore(
              jQuery(
                "#fooevents_printing_widgets div[data-order='" +
                  clonedPosition +
                  "']"
              )
            );

            clonedItem.draggable({
              revert: "invalid",
              helper: "clone",
              zIndex: 1000,
              appendTo: "body",

              start: function (e, ui) {
                origSlot =
                  "#WooCommerceBadgeField" +
                  jQuery(this).parents("td").attr("id");
                tinymce.EditorManager.execCommand(
                  "mceRemoveEditor",
                  false,
                  jQuery(this).find("textarea").attr("id")
                );
              },

              stop: function (e, ui) {
                tinymce.init({
                  selector: "#" + jQuery(this).find("textarea").attr("id"),
                  branding: false,
                  elementpath: false,
                  menubar: false,
                  plugins: "lists",
                  toolbar: [
                    "bold italic bullist numlist alignleft aligncenter alignright",
                  ],
                });
              },
            });

            jQuery(ui.draggable).removeClass("fooevents_printing_widget_init");

            if (
              jQuery(ui.draggable).find("textarea").attr("id") ==
              "WooCommerceEventsPrintTicketCustom"
            ) {
              jQuery(ui.draggable)
                .find("textarea")
                .attr("id", slotID + "_custom");
              jQuery(ui.draggable)
                .find("textarea")
                .attr("name", slotID + "_custom");
            }
          }

          jQuery(this).append(
            ui.draggable.css({
              top: 0,
              left: 0,
            })
          );

          jQuery(ui.draggable).css(
            "width",
            fooevents_calculate_widget_resized_width() + "px"
          );
          jQuery(ui.draggable)
            .find(".fooevents_printing_ticket_select")
            .attr("name", slotID + "_font");
          jQuery(ui.draggable)
            .find(".fooevents_printing_ticket_select")
            .attr("id", slotID + "_font");
          jQuery(ui.draggable)
            .find(".fooevents_printing_widget_options input.uploadfield")
            .attr("name", slotID + "_logo");
          jQuery(ui.draggable)
            .find(".fooevents_printing_widget_options input.uploadfield")
            .attr("id", slotID + "_logo");
          jQuery(ui.draggable)
            .find(".fooevents_printing_widget_options textarea")
            .attr("name", slotID + "_custom");
          jQuery(ui.draggable)
            .find(".fooevents_printing_widget_options textarea")
            .attr("id", slotID + "_custom");
          jQuery(printSlotID).val(
            jQuery(this)
              .find(".fooevents_printing_widget > span")
              .attr("data-name")
          );
          jQuery(origSlot).val("");

          jQuery(this)
            .find(".fooevents_printing_widget > span")
            .unbind("click")
            .on("click", function () {
              fooevents_show_hide_widget_details(jQuery(this));
            });
        } else {
          ui.draggable.animate(
            {
              top: 0,
              left: 0,
            },
            "slow"
          );
        }

        fooevents_validate_slot(jQuery(".ui-selected").not(ui.draggable));
      },
    };

    jQuery(".fooevents_printing_slot").droppable(dropOption);

    function fooevents_validate_slot($draggables) {
      $draggables.each(function () {
        var $target = jQuery(jQuery(this).data("target")).filter(function (
          i,
          elm
        ) {
          return (
            jQuery(this).is(".fooevents_printing_slot") &&
            !jQuery(this).has(".fooevents_printing_widget").length
          );
        });

        if ($target.length) {
          $target.append(
            $(this).css({
              top: 0,
              left: 0,
            })
          );
        } else {
          jQuery(this).animate(
            {
              top: 0,
              left: 0,
            },
            "slow"
          );
        }
      });

      jQuery(".ui-selected")
        .data("original", null)
        .data("target", null)
        .removeClass("ui-selected");
    }
  });

  function fooevents_set_layout_columns(nrCol) {
    switch (nrCol) {
      case "1":
        var tdWidth = "100%";
        break;

      case "2":
        var tdWidth = "50%";
        break;

      case "3":
        var tdWidth = "33.33%";
        break;

      case "4":
        var tdWidth = "25%";
        break;
    }

    jQuery(".fooevents_printing_slot").each(function () {
      jQuery(this).css("width", tdWidth);

      if (jQuery(this).hasClass("hide_col_" + nrCol)) {
        jQuery(this).hide();
        jQuery(this).prev().addClass("no_border_right");
      } else {
        if (
          !jQuery(this).hasClass(
            "hide_row_" + jQuery("#WooCommercePrintTicketNrRows").val()
          )
        )
          jQuery(this).show();

        jQuery(this).prev().removeClass("no_border_right");
      }
    });
  }

  function fooevents_set_layout_rows(nrRow) {
    jQuery(".fooevents_printing_slot").each(function () {
      if (jQuery(this).hasClass("hide_row_" + nrRow)) {
        jQuery(this).hide();
        jQuery(this).parent().hide();
        jQuery(this).parent().prev().find("td").addClass("no_border_bottom");
      } else {
        if (
          !jQuery(this).hasClass(
            "hide_col_" + jQuery("#WooCommercePrintTicketNrColumns").val()
          )
        )
          jQuery(this).show();

        jQuery(this).parent().show();
        jQuery(this).parent().prev().find("td").removeClass("no_border_bottom");
      }
    });
  }
})(jQuery);
