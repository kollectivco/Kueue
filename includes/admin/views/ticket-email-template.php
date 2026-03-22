<!DOCTYPE html>
<html>
<head>
    <style>
        .email-container { font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0073aa; padding-bottom: 15px; }
        .body { padding: 20px 0; line-height: 1.6; }
        .ticket-box { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .button { background: #0073aa; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{event_name}</h1>
        </div>
        <div class="body">
            <p>Hello {attendee_name},</p>
            <p>Your ticket for <strong>{event_name}</strong> has been issued successfully!</p>
            
            <div class="ticket-box">
                <p><strong>Ticket Number:</strong> {ticket_number}</p>
                <p><strong>Date:</strong> {event_date}</p>
                <p><strong>Time:</strong> {event_time}</p>
                <p><strong>Venue:</strong> {venue_name}</p>
                
                <p style="margin-top: 20px;">
                    <a href="{ticket_link}" class="button">View Online Ticket</a>
                </p>
            </div>
            
            <p>If you have any questions, please contact the organizer.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
