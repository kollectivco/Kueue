<?php

namespace KueueEvents\Core\Core;

class Deactivator {

    /**
     * Deactivate the plugin.
     */
    public function deactivate() {
        // Clear scheduled tasks
        wp_clear_scheduled_hook( 'kq_delivery_cron_hook' );
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
