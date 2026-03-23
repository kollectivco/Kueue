function fooeventsposUpdateFooEventsPOSCategoryMultiselect() {
  if (
    jQuery("input[name='globalFooEventsPOSProductsToDisplay']:checked").val() ===
    "cat"
  ) {
    jQuery("select#globalFooEventsPOSProductCategories").removeAttr("disabled");
  } else {
    jQuery("select#globalFooEventsPOSProductCategories").attr(
      "disabled",
      "disabled"
    );
  }
}

jQuery(document).ready(function () {
  jQuery(".fooeventspos-tooltip").tooltip({
    tooltipClass: "fooeventspos-tooltip-box",
  });

  if (jQuery("input[name='globalFooEventsPOSProductsToDisplay']").length > 0) {
    fooeventsposUpdateFooEventsPOSCategoryMultiselect();

    jQuery("input[name='globalFooEventsPOSProductsToDisplay']").change(function () {
      fooeventsposUpdateFooEventsPOSCategoryMultiselect();
    });
  }

  if (jQuery(".upload_image_button_fooeventspos").length > 0) {
    jQuery(".wrap").on("click", ".upload_image_button_fooeventspos", function (e) {
      e.preventDefault();

      var button = jQuery(this);

      wp.media.editor.send.attachment = function (props, attachment) {
        if (button.hasClass("fooeventspos-show-image")) {
          var imageContainer = button
            .closest("tr")
            .find(".fooeventspos-image-container");

          var uploadIDInput = button
            .closest("tr")
            .find(".fooeventspos-hidden-image-input");

          jQuery(imageContainer).html(
            '<img src="' +
              attachment.url +
              "?" +
              Date.now() +
              '" class="fooeventspos-uploaded-image" />'
          );

          uploadIDInput.val(attachment.id);
        } else {
          var uploadInput = button.closest("tr").find(".uploadfield");
          jQuery(uploadInput).val(attachment.url);
        }
      };

      wp.media.editor.open(button);

      return false;
    });

    jQuery(".wrap").on("click", ".upload_reset_fooeventspos", function (e) {
      e.preventDefault();

      if (jQuery(this).hasClass("fooeventspos-show-image")) {
        jQuery(this).closest("tr").find(".fooeventspos-image-container").html("");
        jQuery(this).closest("tr").find(".fooeventspos-hidden-image-input").val("");
      } else {
        jQuery(this).closest("tr").find(".uploadfield").val("");
      }

      return false;
    });

    window.original_send_to_editor = function () {};
  }

  if (jQuery("input#globalFooEventsPOSSquareApplicationID").length) {
    jQuery("input#globalFooEventsPOSSquareApplicationID").keyup(function () {
      var showSquareNotice = false;
      var showSquareSandboxNotice = false;

      if (jQuery(this).val().indexOf("sandbox-") > -1) {
        showSquareNotice = true;
        showSquareSandboxNotice = true;
      }

      jQuery("#fooeventspos_square_notice_row").hide();
      jQuery("#fooeventspos_square_sandbox").hide();

      if (showSquareNotice) {
        jQuery("#fooeventspos_square_notice_row").css("display", "table-row");

        if (showSquareSandboxNotice) {
          jQuery("#fooeventspos_square_sandbox").show();
        }
      }
    });
  }

  if (jQuery("input.fooeventspos-stripe-api-key").length) {
    jQuery("input.fooeventspos-stripe-api-key").keyup(function () {
      var showStripeNotice = false;
      var showStripeShortSecretNotice = false;
      var showStripeTestModeNotice = false;

      jQuery("input.fooeventspos-stripe-api-key").each(function () {
        if (
          jQuery(this).val().indexOf("...") > -1 ||
          jQuery(this).val().indexOf("_test_") > -1
        ) {
          showStripeNotice = true;

          if (jQuery(this).val().indexOf("...") > -1) {
            showStripeShortSecretNotice = true;
          } else if (jQuery(this).val().indexOf("_test_") > -1) {
            showStripeTestModeNotice = true;
          }
        }
      });

      jQuery("#fooeventspos_stripe_notice_row").hide();
      jQuery("#fooeventspos_stripe_short_secret").hide();
      jQuery("#fooeventspos_stripe_test_mode").hide();

      if (showStripeNotice) {
        jQuery("#fooeventspos_stripe_notice_row").css("display", "table-row");

        if (showStripeShortSecretNotice) {
          jQuery("#fooeventspos_stripe_short_secret").show();
        } else if (showStripeTestModeNotice) {
          jQuery("#fooeventspos_stripe_test_mode").show();
        }
      }
    });
  }

  if (jQuery("input.fooeventspos-products-use-decimal-quantities").length) {
    jQuery("input.fooeventspos-products-use-decimal-quantities").change(
      function () {
        if (jQuery(this).prop("checked") === true) {
          jQuery("tr#fooeventspos_decimal_quantity_notice_row").css(
            "display",
            "table-row"
          );
        } else {
          jQuery("tr#fooeventspos_decimal_quantity_notice_row").css(
            "display",
            "none"
          );
        }
      }
    );
  }

  if (
    jQuery("input#fooeventspos_product_override_default_cart_quantity_unit").length
  ) {
    jQuery("input#fooeventspos_product_override_default_cart_quantity_unit").change(
      function () {
        if (jQuery(this).prop("checked") === true) {
          jQuery("p.fooeventspos_product_cart_quantity_unit_field").show();
        } else {
          jQuery("p.fooeventspos_product_cart_quantity_unit_field").hide();
        }
      }
    );
  }

  if (jQuery(".fooeventspos-customer-search").length) {
    var customer_roles = jQuery("select#globalFooEventsPOSCustomerUserRole").val();
    var default_customer_nonce = jQuery(
      "input[name=fooeventspos_default_customer_nonce]"
    ).val();

    jQuery(".fooeventspos-customer-search").select2({
      ajax: {
        url: ajaxurl,
        dataType: "json",
        delay: 250,
        dataType: "json",
        data: function (params) {
          return {
            q: params.term,
            default_customer_nonce: default_customer_nonce,
            customer_roles: JSON.stringify(customer_roles),
            action: "fooeventspos_get_customers",
          };
        },
        processResults: function (data) {
          var options = [];

          if (data) {
            jQuery.each(data, function (index, text) {
              options.push({ id: index, text: text });
            });
          }

          return {
            results: options,
          };
        },
        cache: true,
      },
      minimumInputLength: 3, // the minimum of symbols to input before perform a search
      allowClear: true,
      placeholder:
        fooeventsposPhrases.text_guest + " " + fooeventsposPhrases.text_customer,
    });
  }
});
