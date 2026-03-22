<?php

namespace KueueEvents\Core\Modules\Tickets;

class PDFGenerator {

    /**
     * Generate PDF and output to browser.
     */
    public static function generate_and_output( $ticket ) {
        $renderer = new TemplateRenderer();
        $renderer->set_ticket_data( $ticket );
        
        $template_path = KQ_PLUGIN_DIR . 'includes/Admin/views/ticket-web-view.php';
        if ( ! file_exists( $template_path ) ) {
            wp_die( __( 'Ticket template missing.', 'kueue-events-core' ) );
        }

        $html = $renderer->render( $template_path );

        // Fallback for missing library - Load from vendor if exists
        if ( file_exists( KQ_PLUGIN_DIR . 'includes/Vendor/dompdf/autoload.inc.php' ) ) {
            require_once KQ_PLUGIN_DIR . 'includes/Vendor/dompdf/autoload.inc.php';
        }

        if ( class_exists( '\Dompdf\Dompdf' ) ) {
            $dompdf = new \Dompdf\Dompdf([ 'isRemoteEnabled' => true ]);
            $dompdf->loadHtml( $html );
            $dompdf->setPaper( 'A4', 'portrait' );
            $dompdf->render();
            $dompdf->stream( 'ticket-' . $ticket->ticket_number . '.pdf', [ 'Attachment' => false ] );
            return;
        }

        // Final UI Fallback: Browser Print
        echo $html;
        echo '<script>window.print();</script>';
    }
}
