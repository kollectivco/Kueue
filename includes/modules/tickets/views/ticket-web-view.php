<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e( 'Your Ticket', 'kueue-events-core' ); ?> - {event_name}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .ticket-container { max-width: 400px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .ticket-header { background: #0073aa; color: #fff; padding: 20px; text-align: center; }
        .ticket-header h2 { margin: 0; font-size: 1.5em; }
        .ticket-body { padding: 20px; text-align: center; }
        .ticket-info { margin-top: 15px; text-align: left; }
        .info-row { margin-bottom: 10px; }
        .label { color: #888; font-size: 0.8em; text-transform: uppercase; font-weight: bold; }
        .value { color: #333; font-size: 1.1em; }
        .qr-section { margin: 20px 0; }
        .qr-section img { max-width: 180px; height: auto; }
        .ticket-footer { background: #eee; padding: 15px; text-align: center; font-size: 0.9em; }
        .download-btn { display: inline-block; background: #0073aa; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; margin-top: 20px; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h2>{event_name}</h2>
        </div>
        <div class="ticket-body">
            <div class="qr-section">
                <img src="{qr_code}" alt="QR Code">
                <p class="label">{ticket_number}</p>
            </div>
            <div class="ticket-info">
                <div class="info-row">
                    <p class="label"><?php _e( 'Attendee', 'kueue-events-core' ); ?></p>
                    <p class="value">{attendee_name}</p>
                </div>
                <div class="info-row">
                    <p class="label"><?php _e( 'Ticket Type', 'kueue-events-core' ); ?></p>
                    <p class="value">{ticket_type}</p>
                </div>
                <div class="info-row">
                    <p class="label"><?php _e( 'Date & Time', 'kueue-events-core' ); ?></p>
                    <p class="value">{event_date} - {event_time}</p>
                </div>
                <div class="info-row">
                    <p class="label"><?php _e( 'Venue', 'kueue-events-core' ); ?></p>
                    <p class="value">{venue_name}</p>
                </div>
            </div>
            
            <a href="?kq_pdf=1" class="download-btn no-print"><?php _e( 'Download PDF', 'kueue-events-core' ); ?></a>
        </div>
        <div class="ticket-footer">
            <p><?php _e( 'Please present this ticket at the venue for check-in.', 'kueue-events-core' ); ?></p>
        </div>
    </div>
</body>
</html>
