<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e( 'Your Ticket', 'kueue-events-core' ); ?> - {event_name}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --kq-primary: #ff3131;
            --kq-dark: #0f0f10;
            --kq-light: #f6f6f6;
        }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: var(--kq-light); 
            margin: 0; 
            padding: 40px 20px; 
            color: var(--kq-dark); 
        }
        .ticket-container { 
            max-width: 450px; 
            margin: 0 auto; 
            background: #fff; 
            border-radius: 24px; 
            overflow: hidden; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.1); 
        }
        .ticket-header { 
            background: var(--kq-dark); 
            color: #fff; 
            padding: 30px; 
            text-align: center; 
            border-bottom: 2px dashed #333;
        }
        .ticket-header h2 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; }
        
        .ticket-body { padding: 40px; text-align: center; }
        
        .qr-section { 
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            display: inline-block;
            border: 1px solid #eee;
            margin-bottom: 30px;
        }
        .qr-section img { width: 200px; height: 200px; }
        
        .ticket-number {
            font-family: monospace;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--kq-primary);
            margin-bottom: 40px;
            display: block;
        }

        .ticket-info { text-align: left; }
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 20px; 
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child { border: none; }
        
        .label { color: #888; font-size: 12px; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        .value { color: var(--kq-dark); font-size: 15px; font-weight: 600; }
        
        .ticket-footer { 
            background: #fafafa; 
            padding: 25px; 
            text-align: center; 
            font-size: 13px; 
            color: #666;
            border-top: 1px solid #eee;
        }
        
        .actions {
            max-width: 450px;
            margin: 30px auto 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn { 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--kq-dark); 
            color: #fff; 
            text-decoration: none; 
            padding: 14px; 
            border-radius: 12px; 
            font-weight: 700; 
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn:hover { background: #333; transform: translateY(-2px); }
        .btn-primary { background: var(--kq-primary); }
        .btn-primary:hover { background: #e02a2a; }

        @media print { .no-print { display: none; } body { padding:0; background:#fff; } .ticket-container { box-shadow:none; border: 1px solid #eee; } }
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
            </div>
            <span class="ticket-number">{ticket_number}</span>
            
            <div class="ticket-info">
                <div class="info-row">
                    <span class="label"><?php _e( 'Attendee', 'kueue-events-core' ); ?></span>
                    <span class="value">{attendee_name}</span>
                </div>
                <div class="info-row">
                    <span class="label"><?php _e( 'Category', 'kueue-events-core' ); ?></span>
                    <span class="value">{ticket_type}</span>
                </div>
                <div class="info-row">
                    <span class="label"><?php _e( 'Date', 'kueue-events-core' ); ?></span>
                    <span class="value">{event_date}</span>
                </div>
                <div class="info-row">
                    <span class="label"><?php _e( 'Venue', 'kueue-events-core' ); ?></span>
                    <span class="value">{venue_name}</span>
                </div>
            </div>
        </div>
        <div class="ticket-footer">
            <p><i class="fa fa-info-circle"></i> <?php _e( 'Please present this digital or printed ticket at the entrance.', 'kueue-events-core' ); ?></p>
        </div>
    </div>

    <div class="actions no-print">
        <a href="?kq_pdf=1" class="btn btn-primary"><i class="fa fa-file-pdf"></i> <?php _e( 'Download PDF', 'kueue-events-core' ); ?></a>
        <a href="?kq_wallet=apple" class="btn"><i class="fa-brands fa-apple"></i> <?php _e( 'Apple Wallet', 'kueue-events-core' ); ?></a>
        <a href="{google_wallet_url}" class="btn"><i class="fa-brands fa-google"></i> <?php _e( 'Google Wallet', 'kueue-events-core' ); ?></a>
        <a href="javascript:window.print()" class="btn"><i class="fa fa-print"></i> <?php _e( 'Print Ticket', 'kueue-events-core' ); ?></a>
    </div>
</body>
</html>
