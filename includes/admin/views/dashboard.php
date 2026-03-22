<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1><?php _e( 'Kueue Events Dashboard', 'kueue-events-core' ); ?></h1>
    <div class="welcome-panel">
        <div class="welcome-panel-content">
            <h2>Welcome to Kueue Events Core Foundation</h2>
            <p class="about-description">Manage your events, tickets, and communications from one place.</p>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <h3>Get Started</h3>
                    <ul>
                        <li><a href="<?php echo admin_url( 'edit.php?post_type=kq_event' ); ?>" class="welcome-icon dashicons-calendar-alt">Manage Events</a></li>
                        <li><a href="<?php echo admin_url( 'admin.php?page=kq-tickets-list' ); ?>" class="welcome-icon dashicons-tickets-alt">Manage Tickets</a></li>
                    </ul>
                </div>
                <div class="welcome-panel-column">
                    <h3>Communications</h3>
                    <ul>
                        <li><a href="<?php echo admin_url( 'admin.php?page=kq-sms-accounts' ); ?>" class="welcome-icon dashicons-email-alt">Configure SMS Gateway</a></li>
                        <li><a href="<?php echo admin_url( 'admin.php?page=kq-whatsapp-accounts' ); ?>" class="welcome-icon dashicons-whatsapp">Configure WhatsApp Gateway</a></li>
                    </ul>
                </div>
                <div class="welcome-panel-column welcome-panel-last">
                    <h3>Next Steps</h3>
                    <ul>
                        <li><a href="<?php echo admin_url( 'admin.php?page=kq-settings' ); ?>" class="welcome-icon dashicons-admin-settings">General Settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
