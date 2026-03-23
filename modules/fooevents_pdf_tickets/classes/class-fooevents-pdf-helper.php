<?php

/**
 * PDF generation class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-pdf-tickets
 */

/**
 * PDF Helper Class
 */
class FooEvents_PDF_Helper {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * Barcode output method
	 *
	 * @var string $barcode_output_method
	 */
	public $barcode_output_method;

	/**
	 * On plugin load
	 *
	 * @param array $config config.
	 */
	public function __construct( $config ) {

		$this->config                = $config;
		$this->barcode_output_method = get_option( 'globalWooCommerceEventsBarcodeOutput' );

	}

	/**
	 * Includes email template and parses PHP
	 *
	 * @param string $template
	 * @param array  $customerDetails
	 * @param array  $ticket
	 * @return string
	 */
	public function parse_email_template( $template, $ticket, $customerDetails = array() ) {

		ob_start();
		$themePacksURL = $this->config->theme_packs_url;

		$barcodeURL = '';
		if ( 'ticketnumberformated' == $ticket['WooCommerceEventsTicketIdentifierOutput'] ) {

			$barcodeURL = $this->config->barcode_url . $ticket['barcodeFileName'] . '-tn.png'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

		} else {

			$barcodeURL = $this->config->barcode_url . $ticket['barcodeFileName'] . '.png'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

		}

		$globalFooEventsPDFTicketsFont = get_option( 'globalFooEventsPDFTicketsFont' );
		$font_family                   = $this->get_font_family( $globalFooEventsPDFTicketsFont );
		$font_face                     = $this->get_font_face( $globalFooEventsPDFTicketsFont );
		$font_face                    .= $this->get_font_face_bold( $globalFooEventsPDFTicketsFont );

		$rtl_support = '';
		if ( get_option( 'globalFooEventsPDFTicketsArabicSupport' ) ) {

			$rtl_support = 'direction: rtl; text-align: right;';

		} else {

			$rtl_support = '';

		}

		include $template;

		return ob_get_clean();

	}

	/**
	 * Includes the ticket template and parses PHP.
	 *
	 * @param array  $ticket ticket.
	 * @param string $template template.
	 */
	public function parse_ticket_template( $ticket, $template ) {

		ob_start();

		$plugins_url    = plugins_url();
		$themePacksPath = $this->config->theme_packs_path;
		$themeDetails   = explode( '/', $template );
		$themeDetails   = array_reverse( $themeDetails );
		$themeName      = $themeDetails[1];

		$barcodePath = $this->config->barcode_path;

		$barcode_tn = '';
		if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] ) {

			$barcode_tn = '-tn';

		}

		$barcodeURL = '';
		if ( 'pngjpg' === $this->barcode_output_method ) {

			$barcodeURL = 'data:image/png;base64,' . base64_encode( file_get_contents( $barcodePath . $ticket['barcodeFileName'] . $barcode_tn . '.jpg' ) );

		} else {

			$barcodeURL = 'data:image/png;base64,' . base64_encode( file_get_contents( $barcodePath . $ticket['barcodeFileName'] . $barcode_tn . '.png' ) );

		}

		if ( array_key_exists( 'WooCommerceEventsTicketLogoPath', $ticket ) && ! empty( $ticket['WooCommerceEventsTicketLogoPath'] ) && '1' !== $ticket['WooCommerceEventsTicketLogoPath'] ) {

			$ticket['WooCommerceEventsTicketLogo'] = 'data:image/png;base64,' . base64_encode( file_get_contents( $ticket['WooCommerceEventsTicketLogoPath'] ) );

		}

		if ( array_key_exists( 'WooCommerceEventsTicketHeaderImagePath', $ticket ) && ! empty( $ticket['WooCommerceEventsTicketHeaderImagePath'] ) && '1' !== $ticket['WooCommerceEventsTicketHeaderImagePath'] ) {

			$ticket['WooCommerceEventsTicketHeaderImage'] = 'data:image/png;base64,' . base64_encode( file_get_contents( $ticket['WooCommerceEventsTicketHeaderImagePath'] ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/location.jpg' ) ) ) {

			$locationIcon = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/location.jpg' ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/time.jpg' ) ) ) {

			$timeIcon = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/time.jpg' ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/ticket.jpg' ) ) ) {

			$ticketIcon = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/ticket.jpg' ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/cut1.jpg' ) ) ) {

			$cut1 = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/cut1.jpg' ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/cut2.jpg' ) ) ) {

			$cut2 = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/cut2.jpg' ) );

		}

		if ( file_exists( ( $themePacksPath . $themeName . '/images/divider.jpg' ) ) ) {

			$divider = 'data:image/png;base64,' . base64_encode( file_get_contents( $themePacksPath . $themeName . '/images/divider.jpg' ) );

		}

		$globalFooEventsPDFTicketsFont = get_option( 'globalFooEventsPDFTicketsFont' );
		$font_family                   = $this->get_font_family( $globalFooEventsPDFTicketsFont );
		$font_face                     = $this->get_font_face( $globalFooEventsPDFTicketsFont );

		// Check theme directory for template first.
		if ( file_exists( $this->config->template_path_theme . $template ) ) {

			include $this->config->template_path_theme . $template;

		} else {

			include $template;

		}

		return ob_get_clean();

	}

	/**
	 * Includes the ticket template and parses PHP.
	 *
	 * @param array  $tickets
	 * @param string $template_name
	 */
	public function parse_multiple_ticket_template( $tickets, $template, $eventPluginURL ) {

		ob_start();

		$plugins_url = plugins_url();

		// Check theme directory for template first.
		if ( file_exists( $this->config->template_path_theme . $template ) ) {

			include $this->config->template_path_theme . $template;

		} else {

			include $this->config->template_path . $template;

		}

		return ob_get_clean();

	}

	/**
	 * Generates font family based on selected global font
	 *
	 * @param string $global_fooevents_pdf_tickets_font global font.
	 * @return string
	 */
	private function get_font_family( $global_fooevents_pdf_tickets_font ) {

		switch ( $global_fooevents_pdf_tickets_font ) {

			case 'DejaVu Sans':
				return "'DejaVu Sans','Helvetica'";
			break;

			case 'Firefly Sung':
				return "'Firefly Sung', 'DejaVu Sans','Helvetica'";
			break;

			case 'Noto Sans JP':
				return "'Noto Sans JP', 'Firefly Sung', 'DejaVu Sans','Helvetica'";
			break;

			default:
				return "'DejaVu Sans','Helvetica'";

		}

	}

	/**
	 * Generates font face based on selected global font
	 *
	 * @param string $global_fooevents_pdf_tickets_font global font.
	 * @return string
	 */
	private function get_font_face( $global_fooevents_pdf_tickets_font ) {

		switch ( $global_fooevents_pdf_tickets_font ) {

			case 'DejaVu Sans':
				return '';
			break;

			case 'Firefly Sung':
				return "@font-face {
                    font-family: 'Firefly Sung';
                    font-style: normal;
                    font-weight: 400;
                    src: url(https://www.fooevents.com/fonts/fireflysung.ttf) format('truetype');
                  }";
			break;

			case 'Noto Sans JP':
				return "@font-face {
                    font-family: 'Noto Sans JP';
                    font-style: normal;
                    font-weight: 400;
                    src: url(https://www.fooevents.com/fonts/NotoSansJP-Regular.ttf) format('truetype');
                  }";
			break;

			default:
				return '';

		}

	}

	/**
	 * Generates font face based on selected global font
	 *
	 * @param string $global_fooevents_pdf_tickets_font global font.
	 * @return string
	 */
	private function get_font_face_bold( $global_fooevents_pdf_tickets_font ) {

		switch ( $global_fooevents_pdf_tickets_font ) {

			case 'DejaVu Sans':
				return '';
			break;

			case 'Firefly Sung':
				return "@font-face {
                    font-family: 'Firefly Sung';
                    font-style: bold;
                    font-weight: bold;
                    src: url(https://www.fooevents.com/fonts/fireflysung.ttf) format('truetype');
                  }";
			break;

			case 'Noto Sans JP':
				return "@font-face {
                    font-family: 'Noto Sans JP';
                    font-style: bold;
                    font-weight: bold;
                    src: url(https://www.fooevents.com/fonts/NotoSansJP-Bold.ttf) format('truetype');
                  }";
			break;

			default:
				return '';

		}

	}

}
