(function ($) {
  // Settings
  function initUserOptionChange() {
    if (
      jQuery('input[name="globalWooCommerceEventsZoomSelectedUserOption"]')
        .length > 0
    ) {
      jQuery(
        'input[name="globalWooCommerceEventsZoomSelectedUserOption"]'
      ).change(function () {
        enableDisableUsersSelect(jQuery(this).val());
      });
    }
  }

  function enableDisableUsersSelect(val) {
    if (val === "select") {
      jQuery("select#globalWooCommerceEventsZoomSelectedUsers").removeAttr(
        "disabled"
      );
    } else {
      jQuery("select#globalWooCommerceEventsZoomSelectedUsers").attr(
        "disabled",
        "disabled"
      );
    }
  }

  // Test Access
  if (jQuery("#fooevents_zoom_test_access").length) {
    jQuery(
      "#globalWooCommerceEventsZoomAccountID, #globalWooCommerceEventsZoomClientID, #globalWooCommerceEventsZoomClientSecret"
    ).change(function () {
      jQuery("#globalWooCommerceEventsZoomUsers").hide();

      jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
        '<input type="hidden" name="globalWooCommerceEventsZoomSelectedUsers[]" value="me" />'
      );
    });

    jQuery("#fooevents_zoom_test_access").click(function () {
      jQuery("#globalWooCommerceEventsZoomUsers").hide();

      jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
        '<input type="hidden" name="globalWooCommerceEventsZoomSelectedUsers[]" value="me" />'
      );

      var testButton = jQuery(this);

      jQuery("mark.fooevents-zoom-test-access-result").remove();

      testButton
        .attr("value", zoomObj.testingAccess)
        .prop("disabled", true)
        .after(
          '<img src="' +
            zoomObj.adminURL +
            'images/loading.gif" class="fooevents-ajax-spinner" />'
        );

      var accountID = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomAccountID").val()
      );
      var clientID = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomClientID").val()
      );
      var clientSecret = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomClientSecret").val()
      );

      var data = {
        action: "fooevents_zoom_test_access",
        account_id: accountID,
        client_id: clientID,
        client_secret: clientSecret,
      };

      jQuery.post(ajaxurl, data, function (response) {
        testButton.attr("value", zoomObj.testAccess).prop("disabled", false);

        jQuery(".fooevents-ajax-spinner").remove();

        var response = JSON.parse(response);

        if (response.status == "error") {
          testButton.after(
            '<mark class="error fooevents-zoom-test-access-result"><span class="dashicons dashicons-warning"></span>' +
              response.message +
              "</mark>"
          );
        } else {
          testButton.after(
            '<mark class="yes fooevents-zoom-test-access-result"><span class="dashicons dashicons-yes"></span> ' +
              zoomObj.successFullyConnectedZoomAccount +
              "</mark>"
          );

          jQuery("#globalWooCommerceEventsZoomUsers").show();
        }
      });
    });
  }

  // Users
  initUserOptionChange();

  // Fetch Users for Settings
  if (jQuery("#fooevents_zoom_fetch_users").length) {
    jQuery("#fooevents_zoom_fetch_users").click(function () {
      var fetchButton = jQuery(this);
      var selectedUsers = [];
      var userOption = "select";

      if (jQuery("#globalWooCommerceEventsZoomSelectedUsers").length) {
        selectedUsers = jQuery(
          "#globalWooCommerceEventsZoomSelectedUsers"
        ).val();
      }

      if (
        jQuery(
          'input[name="globalWooCommerceEventsZoomSelectedUserOption"]:checked'
        ).length
      ) {
        userOption = jQuery(
          'input[name="globalWooCommerceEventsZoomSelectedUserOption"]:checked'
        ).val();
      }

      fetchButton.attr("value", zoomObj.fetchingUsers).prop("disabled", true);

      jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
        '<img src="' +
          zoomObj.adminURL +
          'images/loading.gif" class="fooevents-ajax-spinner" />'
      );

      var accountID = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomAccountID").val()
      );
      var clientID = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomClientID").val()
      );
      var clientSecret = jQuery.trim(
        jQuery("#globalWooCommerceEventsZoomClientSecret").val()
      );

      var data = {
        action: "fooevents_zoom_fetch_users",
        account_id: accountID,
        client_id: clientID,
        client_secret: clientSecret,
      };

      jQuery.post(ajaxurl, data, function (response) {
        fetchButton.attr("value", zoomObj.fetchUsers).prop("disabled", false);

        var response = JSON.parse(response);

        if (response.status == "error") {
          jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
            '<mark class="error fooevents-zoom-test-access-result"><span class="dashicons dashicons-warning"></span>' +
              response.message +
              "</mark>"
          );
        } else {
          var userSelect = jQuery(
            '<select name="globalWooCommerceEventsZoomSelectedUsers[]" id="globalWooCommerceEventsZoomSelectedUsers" multiple class="fooevents-multiselect"></select>'
          );

          var userKeys = Object.keys(response.data.users);

          for (var i = 0; i < userKeys.length; i++) {
            var userKey = userKeys[i];
            var user = response.data.users[userKey];

            userSelect.append(
              '<option value="' +
                user.id +
                '">' +
                user.first_name +
                " " +
                user.last_name +
                " - " +
                user.email +
                "</option>"
            );
          }

          userSelect.val(selectedUsers);

          var usersHiddenInput = jQuery(
            '<input type="hidden" name="globalWooCommerceEventsZoomUsers" />'
          );

          usersHiddenInput.val(JSON.stringify(response.data.users));

          jQuery("#globalWooCommerceEventsZoomUsersContainer").empty();

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            '<label><input type="radio" name="globalWooCommerceEventsZoomSelectedUserOption" id="globalWooCommerceEventsZoomSelectedUserOptionMe" value="me"> ' +
              zoomObj.userOptionMe +
              "</label>" +
              "<br/><br/>" +
              '<label><input type="radio" name="globalWooCommerceEventsZoomSelectedUserOption" id="globalWooCommerceEventsZoomSelectedUserOptionSelect" value="select"> ' +
              zoomObj.userOptionSelect +
              "</label>" +
              "<br/><br/>"
          );

          initUserOptionChange();

          jQuery(
            'input[name="globalWooCommerceEventsZoomSelectedUserOption"][value="' +
              userOption +
              '"]'
          ).attr("checked", "checked");

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            userSelect
          );

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            usersHiddenInput
          );

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            "<p>" + zoomObj.userLoadTimes + "</p>"
          );

          enableDisableUsersSelect(userOption);
        }
      });
    });
  }

  // Fetch Users for Event Integration
  if (jQuery("#fooevents_zoom_reload_users").length) {
    jQuery("#fooevents_zoom_reload_users").click(function () {
      var fetchButton = jQuery(this);

      fetchButton.attr("value", zoomObj.fetchingUsers).prop("disabled", true);

      var currentSelectedHost = jQuery(
        "select#WooCommerceEventsZoomHost"
      ).val();

      jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
        '<img src="' +
          zoomObj.adminURL +
          'images/loading.gif" class="fooevents-ajax-spinner" />'
      );

      var data = {
        action: "fooevents_zoom_fetch_users",
      };

      jQuery.post(ajaxurl, data, function (response) {
        fetchButton.attr("value", zoomObj.fetchUsers).prop("disabled", false);

        var response = JSON.parse(response);

        if (response.status == "error") {
          jQuery("#globalWooCommerceEventsZoomUsersContainer").html(
            '<mark class="error fooevents-zoom-test-access-result"><span class="dashicons dashicons-warning"></span>' +
              response.message +
              "</mark>"
          );
        } else {
          var userSelect = jQuery(
            '<select name="WooCommerceEventsZoomHost" id="WooCommerceEventsZoomHost" class="fooevents-search-list">' +
              '<option value="">(' +
              zoomObj.notSet +
              ")</option>" +
              "</select>"
          );

          var userKeys = Object.keys(response.data.users);

          for (var i = 0; i < userKeys.length; i++) {
            var userKey = userKeys[i];
            var user = response.data.users[userKey];

            userSelect.append(
              '<option value="' +
                user.id +
                '" ' +
                (currentSelectedHost == user.id ? "selected" : "") +
                ">" +
                user.first_name +
                " " +
                user.last_name +
                " - " +
                user.email +
                "</option>"
            );
          }

          var usersHiddenInput = jQuery(
            '<input type="hidden" name="globalWooCommerceEventsZoomUsers" />'
          );

          usersHiddenInput.val(JSON.stringify(response.data.users));

          jQuery("#globalWooCommerceEventsZoomUsersContainer").empty();

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            userSelect
          );

          jQuery("#globalWooCommerceEventsZoomUsersContainer").append(
            usersHiddenInput
          );

          userSelect.select2();
        }
      });
    });
  }
})(jQuery);

function enableCaptureAttendeeDetails() {
  jQuery(
    'input[name="WooCommerceEventsCaptureAttendeeDetails"], input[name="WooCommerceEventsCaptureAttendeeEmail"]'
  ).prop("checked", true);

  jQuery(
    ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_disabled"
  ).hide();
  jQuery(
    ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_enabled"
  ).show();
}

var fooeventsZoomMeetingRequests = {};

function selectZoomMeeting(selectID) {
  var zoomMeetingID = jQuery("#" + selectID).val();
  var meetingDetailsContainer = jQuery("#" + selectID + "Details");

  if (zoomMeetingID == "auto") {
    getAutoGenerateZoom("#" + selectID + "Details");
  } else {
    meetingDetailsContainer.html("(" + zoomObj.notSet + ")");
  }

  if (
    fooeventsZoomMeetingRequests[selectID] != undefined &&
    fooeventsZoomMeetingRequests[selectID] != null
  ) {
    fooeventsZoomMeetingRequests[selectID].abort();

    fooeventsZoomMeetingRequests[selectID] = null;
  }

  if (zoomMeetingID != "" && zoomMeetingID != "auto") {
    meetingDetailsContainer.html(
      '<img src="' +
        zoomObj.adminURL +
        'images/loading.gif" class="fooevents-ajax-spinner" />'
    );

    var data = {
      action: "fooevents_fetch_zoom_meeting",
      zoomMeetingID: zoomMeetingID,
    };

    fooeventsZoomMeetingRequests[selectID] = jQuery.post(
      ajaxurl,
      data,
      function (response) {
        fooeventsZoomMeetingRequests[selectID] = null;

        var result = JSON.parse(response);

        if (result.status === "success") {
          var fooeventsZoomMeeting = result.data;

          if (
            fooeventsZoomMeeting.type == 3 ||
            fooeventsZoomMeeting.type == 6
          ) {
            meetingDetailsContainer.html(
              '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
                (fooeventsZoomMeeting.type == 3
                  ? zoomObj.noFixedTimeMeeting
                  : zoomObj.noFixedTimeWebinar) +
                "</mark><br/><a href='https://zoom.us/" +
                (fooeventsZoomMeeting.type == 3 ? "meeting" : "webinar") +
                "/" +
                fooeventsZoomMeeting.id +
                "/edit' target='_blank'>" +
                (fooeventsZoomMeeting.type == 3
                  ? zoomObj.editMeeting
                  : zoomObj.editWebinar) +
                "</a>"
            );
          } else {
            var zoomMeetingInfoTable = jQuery(
              '<table class="fooevents-zoom-meeting-info-table"></table>'
            );

            if (fooeventsZoomMeeting.topic !== "") {
              zoomMeetingInfoTable.append(
                '<tr><th align="left" valign="top">' +
                  zoomObj.topic +
                  ":</th><td>" +
                  fooeventsZoomMeeting.topic +
                  "</td></tr>"
              );
            }

            if (
              fooeventsZoomMeeting.agenda !== "" &&
              fooeventsZoomMeeting.agenda != undefined
            ) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left" valign="top">' +
                  zoomObj.description +
                  ":</th><td>" +
                  fooeventsZoomMeeting.agenda +
                  "</td></tr>"
              );
            }

            if (fooeventsZoomMeeting.start_date_display !== undefined) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left" width="25%">' +
                  (fooeventsZoomMeeting.type == 5 ||
                  fooeventsZoomMeeting.type == 2
                    ? zoomObj.date
                    : zoomObj.startDate) +
                  ":</th><td>" +
                  fooeventsZoomMeeting.start_date_display +
                  "</td></tr>"
              );
            }

            if (fooeventsZoomMeeting.start_time_display !== undefined) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left" width="25%">' +
                  zoomObj.startTime +
                  ":</th><td>" +
                  fooeventsZoomMeeting.start_time_display +
                  "</td></tr>"
              );
            }

            if (fooeventsZoomMeeting.end_time_display !== undefined) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left" width="25%">' +
                  zoomObj.endTime +
                  ":</th><td>" +
                  fooeventsZoomMeeting.end_time_display +
                  "</td></tr>"
              );
            }

            if (fooeventsZoomMeeting.duration_display !== undefined) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left">' +
                  zoomObj.duration +
                  ":</th><td>" +
                  fooeventsZoomMeeting.duration_display +
                  "</td></tr>"
              );
            }

            zoomMeetingInfoTable.append(
              '<tr><th align="left">' +
                zoomObj.registrations +
                ":</th><td>" +
                fooeventsZoomMeeting.registrants.total_records +
                " / " +
                fooeventsZoomMeeting.meeting_capacity +
                "</td></tr>"
            );

            if (
              fooeventsZoomMeeting.type != 5 &&
              fooeventsZoomMeeting.type != 2
            ) {
              zoomMeetingInfoTable.append(
                '<tr><th align="left">' +
                  zoomObj.recurrence +
                  ":</th><td>" +
                  fooeventsZoomMeeting.recurrence.type_display +
                  "</td></tr>"
              );

              var occurrences = jQuery("<td></td>");
              var occurrencesContainer = jQuery(
                '<div class="fooevents-zoom-occurrences-container"></div>'
              );

              if (fooeventsZoomMeeting.occurrences.length == 0) {
                occurrencesContainer.text("(" + zoomObj.noOccurrences + ")");
              } else {
                for (
                  var i = 0;
                  i < fooeventsZoomMeeting.occurrences.length;
                  i++
                ) {
                  var occurrence = fooeventsZoomMeeting.occurrences[i];

                  occurrencesContainer.append(
                    '<span class="' +
                      (occurrence.status == "deleted"
                        ? " fooevents-zoom-occurrence-deleted "
                        : "") +
                      '">' +
                      occurrence.start_date_display +
                      " " +
                      occurrence.start_time_display +
                      " - " +
                      occurrence.end_time_display +
                      " (" +
                      occurrence.duration_display +
                      ")</span>"
                  );

                  occurrencesContainer.append("<br/>");
                }
              }

              occurrences.append(occurrencesContainer);

              zoomMeetingInfoTable.append(
                '<tr><th align="left" valign="top">' +
                  zoomObj.upcomingOccurrences +
                  ":</th><td>" +
                  occurrences.html() +
                  "</td></tr>"
              );
            }

            meetingDetailsContainer.empty();

            if (
              fooeventsZoomMeeting.type === 5 ||
              fooeventsZoomMeeting.type === 2
            ) {
              // Once-off meeting
              if (
                jQuery('input[name="WooCommerceEventsZoomMultiOption"]')
                  .length &&
                jQuery(
                  'input[name="WooCommerceEventsZoomMultiOption"]:checked'
                ).val() === "single" &&
                meetingDetailsContainer.parents("#fooevents_zoom_meeting_multi")
                  .length === 0 &&
                jQuery("#WooCommerceEventsNumDays").val() > 1 &&
                jQuery('input[name="WooCommerceEventsType"]:checked').val() ===
                  "sequential"
              ) {
                meetingDetailsContainer.append(
                  '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
                    (fooeventsZoomMeeting.type === 2
                      ? zoomObj.notRecurringMeeting
                      : zoomObj.notRecurringWebinar) +
                    "</mark><br/>"
                );
              }
            }

            meetingDetailsContainer.append(zoomMeetingInfoTable);

            var registrationResult = jQuery(
              '<span class="fooevents-zoom-registration-result fooevents-zoom-registration-result' +
                zoomMeetingID +
                '" data-meeting-id="' +
                zoomMeetingID +
                '"></span>'
            );

            if (
              fooeventsZoomMeeting.type === 5 ||
              fooeventsZoomMeeting.type === 2
            ) {
              // Once-off meeting
              meetingDetailsContainer.append(
                zoomObj.registrationRequired + "<br/>"
              );

              if (fooeventsZoomMeeting.settings.approval_type === 0) {
                registrationResult.html(
                  '<mark class="yes fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-yes"></span> ' +
                    (fooeventsZoomMeeting.type === 2
                      ? zoomObj.meetingRegistrationCurrentlyEnabled
                      : zoomObj.webinarRegistrationCurrentlyEnabled) +
                    "</mark>"
                );
              } else {
                registrationResult.html(
                  '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
                    (fooeventsZoomMeeting.type === 2
                      ? zoomObj.meetingRegistrationCurrentlyDisabled
                      : zoomObj.webinarRegistrationCurrentlyDisabled) +
                    '</mark><br/><a class="fooevents-enable-zoom-registration-link' +
                    zoomMeetingID +
                    '" href="javascript:fooeventsEnableZoomRegistration(\'' +
                    fooeventsZoomMeeting.id +
                    (fooeventsZoomMeeting.type == 5 ||
                    fooeventsZoomMeeting.type == 6 ||
                    fooeventsZoomMeeting.type == 9
                      ? "_webinars"
                      : "_meetings") +
                    "', 0);\">" +
                    (fooeventsZoomMeeting.type === 2
                      ? zoomObj.enableMeetingRegistration
                      : zoomObj.enableWebinarRegistration) +
                    "</a>"
                );
              }
            } else {
              // Recurring meeting
              meetingDetailsContainer.append(
                zoomObj.registrationRequiredForAllOccurrences + "<br/>"
              );

              if (
                fooeventsZoomMeeting.settings.approval_type === 0 &&
                fooeventsZoomMeeting.settings.registration_type === 1
              ) {
                registrationResult.html(
                  '<mark class="yes fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-yes"></span> ' +
                    zoomObj.registrationAllOccurrencesEnabled +
                    "</mark>"
                );
              } else {
                registrationResult.html(
                  '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
                    zoomObj.registrationAllOccurrencesDisabled +
                    '</mark><br/><a class="fooevents-enable-zoom-registration-link' +
                    zoomMeetingID +
                    '" href="javascript:fooeventsEnableZoomRegistration(\'' +
                    fooeventsZoomMeeting.id +
                    (fooeventsZoomMeeting.type == 5 ||
                    fooeventsZoomMeeting.type == 6 ||
                    fooeventsZoomMeeting.type == 9
                      ? "_webinars"
                      : "_meetings") +
                    "', 1);\">" +
                    zoomObj.enableRegistrationForAllOccurrences +
                    "</a>"
                );
              }
            }

            meetingDetailsContainer.append(registrationResult);
          }
        } else {
          meetingDetailsContainer.html(
            '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
              (zoomMeetingID.indexOf("_meetings")
                ? zoomObj.unableToFetchMeeting
                : zoomObj.unableToFetchWebinar) +
              "</mark>"
          );
        }

        checkMultiDetailsError();
      }
    );
  }
}

function getAutoGenerateZoom(detailsContainerID) {
  var meetingDetailsContainer = jQuery(detailsContainerID);
  var eventType = jQuery('input[name="WooCommerceEventsType"]:checked').val();
  var zoomIntegrationType = jQuery(
    'input[name="WooCommerceEventsZoomMultiOption"]:checked'
  ).val();

  var zoomMeetingInfoTable = jQuery(
    '<table class="fooevents-zoom-meeting-info-table"></table>'
  );

  var topic = jQuery('input[name="post_title"]').val();
  var date = jQuery("#WooCommerceEventsDate").val();
  var startTimeHour = jQuery("#WooCommerceEventsHour").val();
  var startTimeMinutes = jQuery("#WooCommerceEventsMinutes").val();
  var startTimePeriod = jQuery("#WooCommerceEventsPeriod").val();
  var endTimeHour = jQuery("#WooCommerceEventsHourEnd").val();
  var endTimeMinutes = jQuery("#WooCommerceEventsMinutesEnd").val();
  var endTimePeriod = jQuery("#WooCommerceEventsEndPeriod").val();
  var durationMinutes = 0;
  var duration = "0 hours";

  if (
    (startTimePeriod == "" && endTimePeriod == "") ||
    startTimePeriod == endTimePeriod
  ) {
    durationMinutes =
      parseInt(endTimeHour) * 60 +
      parseInt(endTimeMinutes) -
      (parseInt(startTimeHour) * 60 + parseInt(startTimeMinutes));
  } else {
    durationMinutes =
      (parseInt(endTimeHour) +
        (endTimePeriod == "p.m." && parseInt(endTimeHour) < 12 ? 12 : 0)) *
        60 +
      parseInt(endTimeMinutes) -
      (parseInt(startTimeHour) * 60 + parseInt(startTimeMinutes));
  }

  var durationHours = Math.floor(durationMinutes / 60);
  var remainingMinutes = durationMinutes % 60;

  duration =
    durationHours + " " + (durationHours == 1 ? zoomObj.hour : zoomObj.hours);

  if (remainingMinutes > 0) {
    duration +=
      " " +
      remainingMinutes +
      " " +
      (remainingMinutes == 1 ? zoomObj.minute : zoomObj.minutes);
  }

  if (zoomIntegrationType == "multi") {
    var dayNumber = detailsContainerID
      .replace("#WooCommerceEventsZoomWebinarMulti", "")
      .replace("Details", "");

    if (eventType == "select") {
      date = jQuery('input[name="WooCommerceEventsSelectDate[]"]')
        .eq(dayNumber - 1)
        .val();

      if (
        jQuery("input#WooCommerceEventsSelectGlobalTime:checked").length === 0
      ) {
        var eventDateNumber = detailsContainerID
          .replace("#WooCommerceEventsZoomWebinarMulti", "")
          .replace("Details", "");

        startTimeHour = jQuery(
          "#WooCommerceEventsSelectDateHour-" + eventDateNumber
        ).val();
        startTimeMinutes = jQuery(
          "#WooCommerceEventsSelectDateMinutes-" + eventDateNumber
        ).val();
        startTimePeriod = jQuery(
          "#WooCommerceEventsSelectDatePeriod-" + eventDateNumber
        ).val();
        endTimeHour = jQuery(
          "#WooCommerceEventsSelectDateHourEnd-" + eventDateNumber
        ).val();
        endTimeMinutes = jQuery(
          "#WooCommerceEventsSelectDateMinutesEnd-" + eventDateNumber
        ).val();
        endTimePeriod = jQuery(
          "#WooCommerceEventsSelectDatePeriodEnd-" + eventDateNumber
        ).val();

        if (
          (startTimePeriod == "" && endTimePeriod == "") ||
          startTimePeriod == endTimePeriod
        ) {
          durationMinutes =
            parseInt(endTimeHour) * 60 +
            parseInt(endTimeMinutes) -
            (parseInt(startTimeHour) * 60 + parseInt(startTimeMinutes));
        } else {
          durationMinutes =
            (parseInt(endTimeHour) +
              (endTimePeriod == "p.m." && parseInt(endTimeHour) < 12
                ? 12
                : 0)) *
              60 +
            parseInt(endTimeMinutes) -
            (parseInt(startTimeHour) * 60 + parseInt(startTimeMinutes));
        }

        durationHours = Math.floor(durationMinutes / 60);
        remainingMinutes = durationMinutes % 60;

        duration =
          durationHours +
          " " +
          (durationHours == 1 ? zoomObj.hour : zoomObj.hours);

        if (remainingMinutes > 0) {
          duration +=
            " " +
            remainingMinutes +
            " " +
            (remainingMinutes == 1 ? zoomObj.minute : zoomObj.minutes);
        }
      }

      topic +=
        " - " +
        date +
        " " +
        startTimeHour +
        ":" +
        startTimeMinutes +
        (startTimePeriod != "" ? " " + startTimePeriod : "");
    } else if (eventType == "sequential") {
      dayNumber--;

      var eventDate = jQuery("#WooCommerceEventsDate").val();
      var dateParts = null;

      if (
        zoomObj.dateFormat == "dd/mm/yy" ||
        zoomObj.dateFormat == "dd-mm-yy"
      ) {
        if (eventDate.includes("/")) {
          dateParts = eventDate.split("/");
        } else if (eventDate.includes("-")) {
          dateParts = eventDate.split("-");
        }
      }

      if (dateParts != null) {
        // Swop day and month so that Date parse works
        var newDateParts = [dateParts[1], dateParts[0], dateParts[2]];

        eventDate = newDateParts.join("/");
      }

      var dateTimestamp = Date.parse(eventDate);
      var dateObj = new Date(dateTimestamp);

      dateObj.setDate(dateObj.getDate() + dayNumber);

      date = dateObj.toDateString();

      topic +=
        " - " +
        date +
        " " +
        startTimeHour +
        ":" +
        startTimeMinutes +
        (startTimePeriod != "" ? " " + startTimePeriod : "");
    }
  }

  zoomMeetingInfoTable.append(
    '<tr><th align="left" valign="top">' +
      zoomObj.topic +
      ":</th><td>" +
      topic +
      "</td></tr>"
  );

  zoomMeetingInfoTable.append(
    '<tr><th align="left" width="25%">' +
      (zoomIntegrationType == "sequential" ? zoomObj.startDate : zoomObj.date) +
      ":</th><td>" +
      date +
      "</td></tr>"
  );

  zoomMeetingInfoTable.append(
    '<tr><th align="left" width="25%">' +
      zoomObj.startTime +
      ":</th><td>" +
      startTimeHour +
      ":" +
      startTimeMinutes +
      (startTimePeriod != "" ? " " + startTimePeriod : "") +
      "</td></tr>"
  );

  zoomMeetingInfoTable.append(
    '<tr><th align="left" width="25%">' +
      zoomObj.endTime +
      ":</th><td>" +
      endTimeHour +
      ":" +
      endTimeMinutes +
      (endTimePeriod != "" ? " " + endTimePeriod : "") +
      "</td></tr>"
  );

  zoomMeetingInfoTable.append(
    '<tr><th align="left">' +
      zoomObj.duration +
      ":</th><td>" +
      duration +
      "</td></tr>"
  );

  if (eventType == "sequential" && zoomIntegrationType == "single") {
    zoomMeetingInfoTable.append(
      '<tr><th align="left">' +
        zoomObj.recurrence +
        ":</th><td>" +
        zoomObj.daily +
        "</td></tr>"
    );

    var eventDate = jQuery("#WooCommerceEventsDate").val();
    var dateParts = null;

    if (zoomObj.dateFormat == "dd/mm/yy" || zoomObj.dateFormat == "dd-mm-yy") {
      if (eventDate.includes("/")) {
        dateParts = eventDate.split("/");
      } else if (eventDate.includes("-")) {
        dateParts = eventDate.split("-");
      }
    }

    if (dateParts != null) {
      // Swop day and month so that Date parse works
      var newDateParts = [dateParts[1], dateParts[0], dateParts[2]];

      eventDate = newDateParts.join("/");
    }

    var startDateValue = eventDate;
    var startDateTimestamp = Date.parse(startDateValue);
    var startDateObj = new Date(startDateTimestamp);

    zoomMeetingInfoTable.append(
      '<tr><th align="left" width="25%">' +
        zoomObj.endDate +
        ":</th><td>" +
        jQuery("#WooCommerceEventsEndDate").val() +
        "</td></tr>"
    );

    var occurrences = jQuery("<td></td>");
    var occurrencesContainer = jQuery(
      '<div class="fooevents-zoom-occurrences-container"></div>'
    );

    var numDays = parseInt(jQuery("#WooCommerceEventsNumDays").val());

    for (var day = 0; day < numDays; day++) {
      startDateObj.setDate(new Date(startDateTimestamp).getDate() + day);

      occurrencesContainer.append(
        "<span>" +
          startDateObj.toDateString() +
          " " +
          startTimeHour +
          ":" +
          startTimeMinutes +
          (startTimePeriod != "" ? " " + startTimePeriod : "") +
          " - " +
          endTimeHour +
          ":" +
          endTimeMinutes +
          (endTimePeriod != "" ? " " + endTimePeriod : "") +
          " (" +
          duration +
          ")</span>"
      );

      occurrencesContainer.append("<br/>");
    }

    occurrences.append(occurrencesContainer);

    zoomMeetingInfoTable.append(
      '<tr><th align="left" valign="top">' +
        zoomObj.occurrences +
        ":</th><td>" +
        occurrences.html() +
        "</td></tr>"
    );
  }

  meetingDetailsContainer.empty();

  meetingDetailsContainer.append(zoomMeetingInfoTable);

  var registrationNote = zoomObj.automaticRegistration;

  if (eventType == "sequential") {
    if (zoomIntegrationType == "single") {
      registrationNote = zoomObj.automaticRegistrationAllOccurrences;
    }
  }

  meetingDetailsContainer.append(registrationNote);

  meetingDetailsContainer.append(
    '<br/><br/><input type="button" value="' +
      zoomObj.refreshExampleInfo +
      '" class="button button-secondary fooevents-zoom-integration-button" onclick="javascript:getAutoGenerateZoom(\'' +
      detailsContainerID +
      "');\">"
  );
}

function fooeventsEnableZoomRegistration(zoomMeetingID, recurringMeeting) {
  jQuery(
    "a.fooevents-enable-zoom-registration-link" + zoomMeetingID
  ).replaceWith(
    '<img src="' +
      zoomObj.adminURL +
      'images/loading.gif" class="fooevents-ajax-spinner" />'
  );

  var data = {
    action: "fooevents_update_zoom_registration",
    zoomMeetingID: zoomMeetingID,
    recurringMeeting: recurringMeeting,
  };

  jQuery.post(ajaxurl, data, function (response) {
    var result = JSON.parse(response);

    if (result.status === "success") {
      jQuery(".fooevents-zoom-registration-result" + zoomMeetingID).html(
        '<mark class="yes fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-yes"></span> ' +
          (recurringMeeting === 1
            ? zoomObj.registrationAllOccurrencesEnabled
            : zoomMeetingID.indexOf("_meetings")
            ? zoomObj.meetingRegistrationCurrentlyEnabled
            : zoomObj.webinarRegistrationCurrentlyEnabled) +
          "</mark>"
      );
    } else {
      meetingDetailsContainer.html(
        '<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> ' +
          zoomObj.unableToFetchMeeting +
          '</mark><br/><a class="fooevents-enable-zoom-registration-link' +
          zoomMeetingID +
          '" href="javascript:fooeventsEnableZoomRegistration(\'' +
          fooeventsZoomMeeting.id +
          (fooeventsZoomMeeting.type == 5 ||
          fooeventsZoomMeeting.type == 6 ||
          fooeventsZoomMeeting.type == 9
            ? "_webinars"
            : "_meetings") +
          "', " +
          recurringMeeting +
          ');">' +
          recurringMeeting ===
          0
          ? fooeventsZoomMeeting.type == 5 ||
            fooeventsZoomMeeting.type == 6 ||
            fooeventsZoomMeeting.type == 9
            ? zoomObj.enableWebinarRegistration
            : zoomObj.enableMeetingRegistration
          : zoomObj.enableRegistrationForAllOccurrences + "</a>"
      );
    }
  });
}

function fooeventsZoomShowHideMulti() {
  var multiOption = jQuery(
    'input[name="WooCommerceEventsZoomMultiOption"]:checked'
  ).val();

  jQuery(".zoom-integration-type-container").hide();
  jQuery("#fooevents_zoom_meeting_" + multiOption).show();
}

function initShowHideMultiDetails() {
  jQuery("a.fooevents-zoom-show-hide-meeting-details-link").click(function (e) {
    e.preventDefault();

    var selectID = jQuery(this).attr("data-meeting");
    var meetingDetailsContainer = jQuery("#" + selectID + "Details");

    if (jQuery(this).hasClass("show")) {
      // Hide details
      meetingDetailsContainer.stop().slideUp();

      jQuery(this)
        .removeClass("show")
        .find("span.fooevents-zoom-show-hide-meeting-details-link-text")
        .text(zoomObj.showDetails);
    } else {
      // Show details
      meetingDetailsContainer.stop().slideDown();

      jQuery(this)
        .addClass("show")
        .find("span.fooevents-zoom-show-hide-meeting-details-link-text")
        .text(zoomObj.hideDetails);
    }
  });

  jQuery(".fooevents-zoom-multi-meeting-details-container")
    .text("(" + zoomObj.notSet + ")")
    .hide();
}

function checkMultiDetailsError() {
  var x = 1;

  jQuery(
    "#fooevents_zoom_meeting_multi .fooevents-zoom-registration-result"
  ).each(function () {
    if (jQuery(this).find("mark.error").length) {
      var selectID = jQuery(
        'a[data-meeting="WooCommerceEventsZoomWebinarMulti' + x + '"]'
      ).attr("data-meeting");
      var meetingDetailsContainer = jQuery("#" + selectID + "Details");

      // Show details
      meetingDetailsContainer.show();

      jQuery('a[data-meeting="WooCommerceEventsZoomWebinarMulti' + x + '"]')
        .addClass("show")
        .find("span.fooevents-zoom-show-hide-meeting-details-link-text")
        .text(zoomObj.hideDetails);
    }

    x++;
  });
}

function initZoomSelectChange() {
  if (jQuery("select.WooCommerceEventsZoomSelect").length) {
    jQuery("select.WooCommerceEventsZoomSelect")
      .off("change")
      .change(function () {
        var selectID = jQuery(this).attr("id");

        selectZoomMeeting(selectID);

        if (jQuery('a[data-meeting="' + selectID + '"]').length) {
          jQuery('a[data-meeting="' + selectID + '"]')
            .removeClass("show")
            .click();
        }
      });

    jQuery("select.WooCommerceEventsZoomSelect").each(function () {
      selectZoomMeeting(jQuery(this).attr("id"));
    });
  }

  if (jQuery(".fooevents-search-list").length) {
    jQuery(".fooevents-search-list").select2();
  }
}

(function ($) {
  // Event Integration Settings
  initZoomSelectChange();

  jQuery('input[name="WooCommerceEventsCaptureAttendeeDetails"]').change(
    function () {
      if (jQuery("#fooevents_enable_attendee_details_note").length > 0) {
        if (
          jQuery(
            'input[name="WooCommerceEventsCaptureAttendeeDetails"]:checked'
          ).length == 0
        ) {
          jQuery(
            ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_disabled"
          ).show();
          jQuery(
            ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_enabled"
          ).hide();
        } else {
          jQuery(
            ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_disabled"
          ).hide();
          jQuery(
            ".fooevents_enable_attendee_details_note .fooevents_capture_attendee_details_enabled"
          ).show();
        }
      }
    }
  );

  // Multi-day
  if (jQuery('input[name="WooCommerceEventsZoomMultiOption"]').length) {
    jQuery('input[name="WooCommerceEventsZoomMultiOption"]').change(
      function () {
        fooeventsZoomShowHideMulti();
      }
    );

    fooeventsZoomShowHideMulti();
  }

  if (jQuery("#WooCommerceEventsNumDays").length) {
    jQuery("#WooCommerceEventsNumDays").change(function () {
      var numDays = jQuery(this).val();

      jQuery("#fooevents_zoom_meeting_multi").empty();

      for (var x = 1; x <= numDays; x++) {
        var formField = jQuery('<p class="form-field"></p>');

        if (x == 1) {
          formField.append(
            "<label>" + zoomObj.linkMultiMeetingsWebinars + "</label>"
          );
        }

        formField.append(
          '<span class="fooevents-zoom-day-override-title">' +
            jQuery("#fooevents_zoom_meeting_multi").attr("data-day-term") +
            " " +
            x
        );

        var zoomSelectClone = jQuery("select#WooCommerceEventsZoomWebinar")
          .clone()
          .attr("class", "WooCommerceEventsZoomSelect fooevents-search-list");

        zoomSelectClone.val("");

        zoomSelectClone.attr("name", "WooCommerceEventsZoomWebinarMulti[]");
        zoomSelectClone.attr("id", "WooCommerceEventsZoomWebinarMulti" + x);

        formField.append(zoomSelectClone);

        var showHideDetailsLink = jQuery(
          '<a href="#" class="fooevents-zoom-show-hide-meeting-details-link" data-meeting="WooCommerceEventsZoomWebinarMulti' +
            x +
            '"><span class="toggle-indicator fooevents-zoom-show-hide-meeting-details" aria-hidden="true"></span>' +
            '<span class="fooevents-zoom-show-hide-meeting-details-link-text">' +
            zoomObj.showDetails +
            "</span></a>"
        );

        formField.append(showHideDetailsLink);

        jQuery("#fooevents_zoom_meeting_multi").append(formField);

        jQuery("#fooevents_zoom_meeting_multi").append(
          '<p class="form-field fooevents-zoom-multi-meeting-details">' +
            '<span class="fooevents-zoom-multi-meeting-details-container" id="WooCommerceEventsZoomWebinarMulti' +
            x +
            'Details">(' +
            zoomObj.notSet +
            ")</span></p>"
        );
      }

      initZoomSelectChange();
      initShowHideMultiDetails();
    });

    initShowHideMultiDetails();
  }

  jQuery('input[name="WooCommerceEventsZoomMultiOption"]').change(function () {
    var zoomIntegration = jQuery(
      'input[name="WooCommerceEventsZoomMultiOption"]:checked'
    ).val();

    if (jQuery(".fooevents_zoom_id_label").length) {
      if (zoomIntegration == "bookings") {
        jQuery(".fooevents_zoom_id_label").show();
      } else {
        jQuery(".fooevents_bookings_zoom_id:checked").trigger("click");

        jQuery(".fooevents_zoom_id_label").hide();
      }
    }
  });

  if (jQuery('input[name="WooCommerceEventsType"]').length) {
    jQuery('input[name="WooCommerceEventsType"]').change(function () {
      updateZoomIntegrations();
    });
  }

  function updateZoomIntegrations() {
    var eventTypeRadio = jQuery('input[name="WooCommerceEventsType"]:checked');

    var eventType = eventTypeRadio.val();
    var eventTypeLabel = zoomObj[eventType + "EventType"];
    var eventTypeDescription = zoomObj[eventType + "EventTypeDescription"];

    var currentZoomIntegration = jQuery(
      ".fooevents-zoom-integration-type-container input[type='radio']:checked"
    );

    jQuery("#fooevents_zoom_current_event_type").html(
      "<strong>" + eventTypeLabel + "</strong><br/>" + eventTypeDescription
    );

    jQuery(
      ".fooevents-zoom-integration-type-container span input[type='radio']"
    ).attr("disabled", "disabled");

    jQuery(".fooevents-zoom-integration-type-container span label").css(
      "opacity",
      "0.5"
    );

    jQuery(".zoom-integration-type-container").hide();

    jQuery(
      ".fooevents-zoom-integration-type-container span.zoom-" +
        eventType +
        " input[type='radio']"
    ).removeAttr("disabled");

    jQuery(
      ".fooevents-zoom-integration-type-container span.zoom-" +
        eventType +
        " label"
    ).css("opacity", "1");

    if (currentZoomIntegration.attr("disabled") == "disabled") {
      jQuery(
        ".fooevents-zoom-integration-type-container span.zoom-" +
          eventType +
          ":first input[type='radio']"
      )
        .click()
        .change();
    } else {
      currentZoomIntegration.click().change();
    }
  }

  updateZoomIntegrations();
})(jQuery);
