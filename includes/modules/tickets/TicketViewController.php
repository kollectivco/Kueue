<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketViewController {

    public function run() {
        add_action( 'init', [ $this, 'register_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
        add_action( 'template_include', [ $this, 'handle_ticket_view' ] );
    }

    /**
     * Rewrite rule for /kq-ticket/{token}
     */
    public function register_rewrite_rules() {
        add_rewrite_rule( 'kq-ticket/([^/]+)/?', 'index.php?kq_ticket_token=$matches[1]', 'top' );
        // Also support PDF download
        add_rewrite_rule( 'kq-ticket/([^/]+)/pdf/?', 'index.php?kq_ticket_token=$matches[1]&kq_pdf=1', 'top' );
    }

    /**
     * Query vars for token and pdf.
     */
    public function register_query_vars( $vars ) {
        $vars[] = 'kq_ticket_token';
        $vars[] = 'kq_pdf';
        return $vars;
    }

    /**
     * Handle the view / rendering.
     */
    public function handle_ticket_view( $template ) {
        $token = get_query_var( 'kq_ticket_token' );
        if ( ! $token ) {
            return $template;
        }

        $ticket = TicketRepository::get_by_secure_token( $token );
        if ( ! $ticket || $ticket->ticket_status !== 'active' ) {
            return $template; // Fallback to 404 naturally
        }

        // Handle PDF request
        if ( get_query_var( 'kq_pdf' ) === '1' ) {
            PDFGenerator::generate_and_output( $ticket );
            exit;
        }

        // Render HTML ticket
        $this->render_html_ticket( $ticket );
        exit;
    }

    /**
     * Render the mobile-friendly HTML ticket page.
     */
    protected function render_html_ticket( $ticket ) {
        $renderer = new TemplateRenderer();
        $renderer->set_ticket_data( $ticket );
        
        $template_path = KQ_PLUGIN_DIR . 'includes/Modules/Tickets/views/ticket-web-view.php';
        if ( ! file_exists( $template_path ) ) {
            wp_die( __( 'Ticket template missing.', 'kueue-events-core' ) );
        }

        $html = $renderer->render( $template_path );
        echo $html;
    }
}
