<?php

namespace KueueEvents\Core\Modules\Tickets;

class PDFGenerator {

    /**
     * Generate PDF and output to browser.
     */
    public static function generate_and_output( $ticket ) {
        $renderer = new TemplateRenderer();
        $renderer->set_ticket_data( $ticket );
        
        $template_path = KQ_PLUGIN_DIR . 'includes/admin/views/ticket-web-view.php';
        if ( ! file_exists( $template_path ) ) {
            wp_die( __( 'Ticket template missing.', 'kueue-events-core' ) );
        }

        $html = $renderer->render( $template_path );

        // If dompdf is available (assuming it would be included by the user via composer)
        if ( class_exists( '\Dompdf\Dompdf' ) ) {
            $dompdf = new \Dompdf\Dompdf([ 'isRemoteEnabled' => true ]);
            $dompdf->loadHtml( $html );
            $dompdf->setPaper( 'A4', 'portrait' );
            $dompdf->render();
            $dompdf->stream( 'ticket-' . $ticket->ticket_number . '.pdf', [ 'Attachment' => true ] );
            return;
        }

        // Fallback or debug mode (for now)
        wp_die( __( 'PDF Generator library (Dompdf) not found. Please install it with composer to enable PDF tickets.', 'kueue-events-core' ) );
    }
}
