(function($) {
    
    jQuery('#fooevents-add-ticket-container').on('change', '#WooCommerceEventsEvent', function(e) {
       
        var event = jQuery(this);
        
        fooevents_attendee_fields_admin_add_ticket_get_booking_options(event);
        
    });
    
    function fooevents_attendee_fields_admin_add_ticket_get_booking_options(event) {
        
        var event_id = event.find(":selected").val();

        var data = {
                'action': 'fooevents_fetch_add_ticket_attendee_options',
                'event_id': event_id
            };
        
        jQuery.post(FooEventsAttendeeAddTicketObj.ajaxurl, data, function(response) {
   
            jQuery('#fooevents-add-ticket-attendee-fields-container').html(response);
            
        });
        
    }
    
})(jQuery);