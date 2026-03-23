<?php
/**
 * Email helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email helper class
 */
class FooEvents_Mail_Helper {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	public $config;

	/**
	 * On class load
	 *
	 * @param array $config config values.
	 */
	public function __construct( $config ) {

		$this->config = $config;

	}

	/**
	 * Includes email template and parses PHP
	 *
	 * @param string $template template.
	 * @param array  $ticket ticket.
	 * @param array  $customer_details customer details.
	 * @return string
	 */
	public function parse_email_template( $template, $ticket, $customer_details = array() ) {

		ob_start();

		$font_family = "'DejaVu Sans','Helvetica'";
		$font_face   = "'DejaVu Sans','Helvetica'";

		$theme_details = explode( '/', $template );
		$theme_details = array_reverse( $theme_details );
		$theme_name    = $theme_details[1];
		$template_name = $theme_details[0];

		$theme_packs_url = $this->config->theme_packs_url;
		include $template;

		return ob_get_clean();

	}

	/**
	 * Includes the ticket template and parses PHP.
	 *
	 * @param string $template template.
	 * @param array  $ticket ticket.
	 * @param bool   $preview_ticket preview ticket.
	 */
	public function parse_ticket_template( $template, $ticket, $merge_fields = array(), $preview_ticket = false ) {

		ob_start();
		$theme_packs_url  = $this->config->theme_packs_url;
		$event_plugin_url = $this->config->event_plugin_url;

		$font_family = "'DejaVu Sans','Helvetica'";
		$font_face   = "'DejaVu Sans','Helvetica'";

		$theme_details = explode( '/', $template );
		$theme_details = array_reverse( $theme_details );
		$theme_name    = $theme_details[1];
		$template_name = $theme_details[0];

		if ( true === $preview_ticket ) {

			$barcodeURL = $this->config->barcode_url . 'barcode-barcode.png'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
			if ( 'default_pdf_single' !== $theme_name && 'default_pdf_multiple' !== $theme_name ) {

				$ticket['WooCommerceEventsTicketLogo'] = $theme_packs_url . $theme_name . '/images/logo.jpg';

			}
			$ticket['WooCommerceEventsTicketHeaderImage'] = $theme_packs_url . $theme_name . '/images/header_img.jpg';

		} else {

			if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] ) {

				$barcodeURL = $this->config->barcode_url . $ticket['barcodeFileName'] . '-tn.png'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

			} else {

				$barcodeURL = $this->config->barcode_url . $ticket['barcodeFileName'] . '.png'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

			}
		}

		$locationIcon = $theme_packs_url . $theme_name . '/images/location.jpg'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$timeIcon     = $theme_packs_url . $theme_name . '/images/time.jpg'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$ticketIcon   = $theme_packs_url . $theme_name . '/images/ticket.jpg'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		$cut1         = $theme_packs_url . $theme_name . '/images/cut1.jpg';
		$cut2         = $theme_packs_url . $theme_name . '/images/cut2.jpg';
		$divider      = $theme_packs_url . $theme_name . '/images/divider.jpg';

		if ( file_exists( $this->config->email_template_path_theme_email . $theme_name . '/' . $template_name ) ) {

			include $this->config->email_template_path_theme_email . $theme_name . '/' . $template_name;

		} else {

			include $template;

		}

		return ob_get_clean();

	}

	/**
	 * Sends ticket
	 *
	 * @param string $to email to.
	 * @param string $subject email subject.
	 * @param string $body email body.
	 * @param array  $attachments email attachment.
	 * @param int    $product_id the product ID.
	 */
	public function send_ticket( $to, $subject, $body, $attachments = array(), $product_id = 0 ) {

		$subject = html_entity_decode( $subject );

		add_filter( 'wp_mail_content_type', array( $this, 'wpdocs_set_html_mail_content_type' ) );

		$send_copy_to_csv                             = '';
		$global_woocommerce_events_email_ticket_admin = get_option( 'globalWooCommerceEventsEmailTicketAdmin', true );
		$woocommerce_events_email_ticket_admin        = get_post_meta( $product_id, 'wooCommerceEventsEmailTicketAdmin', true );

		if ( ! empty( $woocommerce_events_email_ticket_admin ) && '1' !== $woocommerce_events_email_ticket_admin ) {

			$send_copy_to_csv = $woocommerce_events_email_ticket_admin;

		} elseif ( ! empty( $global_woocommerce_events_email_ticket_admin ) && '1' !== $global_woocommerce_events_email_ticket_admin ) {

			$send_copy_to_csv = $global_woocommerce_events_email_ticket_admin;

		}

		$from = get_option( 'woocommerce_email_from_name' ) . ' <' . sanitize_email( get_option( 'woocommerce_email_from_address' ) ) . '>';

		$headers  = 'Content-type: text/html;charset=utf-8' . "\r\n";
		$headers .= 'From: ' . $from;

		$send_mail = wp_mail( $to, $subject, $body, $headers, $attachments );

		if ( 1 == $send_copy_to_csv ) {

			$send_copy_to_csv = '';

		}

		if ( ! empty( $send_copy_to_csv ) ) {

			$send_copy_to = str_getcsv( $send_copy_to_csv );

			foreach ( $send_copy_to as $to ) {

				if ( ! empty( trim( $to ) ) ) {

					$send_mail = wp_mail( trim( $to ), $subject, $body, $headers, $attachments );

				}
			}
		}

		remove_filter( 'wp_mail_content_type', array( $this, 'wpdocs_set_html_mail_content_type' ) );

		if ( $send_mail ) {

			return true;

		} else {

			return false;

		}

	}

	/**
	 * Sets WordPress mail content type
	 *
	 * @return string
	 */
	public function wpdocs_set_html_mail_content_type() {

		return 'text/html';

	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="updated"><p>' . esc_attr( $notice ) . '</p></div>';

		}

	}
}
