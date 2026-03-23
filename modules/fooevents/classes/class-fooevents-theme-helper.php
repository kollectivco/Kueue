<?php
/**
 * Theme helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme helper class
 */
class FooEvents_Theme_Helper {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	public $config;

	/**
	 * Mail helper object
	 *
	 * @var object $mail_helper Mail helper object
	 */
	public $mail_helper;

	/**
	 * Barcode helper object
	 *
	 * @var object $barcode_helper Barcode helper object
	 */
	private $barcode_helper;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

		// Mail Helper.
		require_once $this->config->class_path . 'class-fooevents-mail-helper.php';
		$this->mail_helper = new FooEvents_Mail_Helper( $this->config );
	}

	/**
	 * Add admin ticket themes menu item
	 */
	public function add_menu_item() {

		add_submenu_page( 'fooevents', __( 'Ticket Themes', 'woocommerce-events' ), __( 'Ticket Themes', 'woocommerce-events' ), 'update_plugins', 'fooevents-ticket-themes', array( $this, 'display_ticket_themes_page' ) );
	}

	/**
	 * Display ticket themes page
	 */
	public function display_ticket_themes_page() {

		if ( ! empty( $_POST['fooevents-theme-viewer-preview-input'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$this->send_preview( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		if ( ! empty( $_FILES['fooevents-theme-viewer-upload-file'] ) ) {

			$this->upload_themes( $_FILES );

		}

		$themes = $this->get_ticket_themes();

		$user       = wp_get_current_user();
		$user_email = $user->user_email;

		include $this->config->template_path . 'ticket-themes-viewer.php';
	}

	/**
	 * Upload new ticket theme
	 *
	 * @param array $form form.
	 */
	public function upload_themes( $form ) {

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'fooevents_upload_theme' ) ) {

			wp_die( __( 'NONCE authentication failed. Please try again.', 'woocommerce-events' ) );

		}

		if ( ! empty( $form['fooevents-theme-viewer-upload-file'] ) ) {

			$type = '';
			if ( isset( $_FILES['fooevents-theme-viewer-upload-file']['type'] ) ) {

				$type = sanitize_text_field( wp_unslash( $_FILES['fooevents-theme-viewer-upload-file']['type'] ) );

			}

			$accepted = false;

			$accepted_file_types = array( 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed' );
			foreach ( $accepted_file_types as $file_type ) {

				if ( $file_type === $type ) {
					$accepted = true;
					break;
				}
			}

			if ( $accepted ) {

				$filename = explode( '.', sanitize_text_field( wp_unslash( $_FILES['fooevents-theme-viewer-upload-file']['name'] ) ) );
				$filename = preg_replace( '/\([^)]+\)/', '', $filename );

				$path = $this->config->theme_packs_path . sanitize_text_field( wp_unslash( $_FILES['fooevents-theme-viewer-upload-file']['name'] ) );

				$already_exists = false;
				if ( file_exists( $this->config->theme_packs_path . $filename[0] ) ) {

					$already_exists = true;
					$this->output_notices( array( __( 'Theme already exists.', 'woocommerce-events' ) ) );

				}

				if ( ! $already_exists ) {

					move_uploaded_file( sanitize_text_field( $_FILES['fooevents-theme-viewer-upload-file']['tmp_name'] ), $path );

					if ( file_exists( $path ) ) {

						$zip    = new ZipArchive();
						$status = $zip->open( $path );

						if ( true === $status ) {

							$zip->extractTo( $this->config->theme_packs_path );
							$zip->close();
							$theme_path = $this->config->theme_packs_path . $filename[0];

							if ( file_exists( $theme_path . '/header.php' ) && file_exists( $theme_path . '/footer.php' ) && file_exists( $theme_path . '/ticket.php' ) ) {

								$this->output_notices( array( __( 'Theme has been uploaded.', 'woocommerce-events' ) ) );

							} else {

								$this->output_notices( array( __( 'File does not contain valid theme files.', 'woocommerce-events' ) ) );

							}

							unlink( $path );

						}
					}
				}
			} else {

				$this->output_notices( array( __( 'Please upload valid .zip file', 'woocommerce-events' ) ) );

			}
		}
	}

	/**
	 * Send ticket theme preview
	 *
	 * @param array $form form.
	 */
	public function send_preview( $form ) {

		if ( ! empty( $form['fooevents-theme-viewer-preview-input'] ) && ! empty( $form['fooevents-theme-viewer-preview-path'] ) ) {

			$theme      = $form['fooevents-theme-viewer-preview-path'];
			$send_to    = $form['fooevents-theme-viewer-preview-input'];
			$theme_name = $form['fooevents-theme-viewer-preview-theme-name'];

			$ticket = $this->get_demo_ticket();

			$header = $this->mail_helper->parse_email_template( $theme . '/header.php', $ticket, array() );
			$footer = $this->mail_helper->parse_email_template( $theme . '/footer.php', $ticket, array() );
			$body   = $this->mail_helper->parse_ticket_template( $theme . '/ticket.php', $ticket, array(), true );

			$subject = $theme_name . ': Preview Ticket';

			$mail_status = $this->mail_helper->send_ticket( $send_to, $subject, $header . $body . $footer, 0 );

			$this->output_notices( array( __( 'Preview ticket has been sent', 'woocommerce-events' ) ) );

		}
	}

	/**
	 * Get demo details for ticket preview
	 *
	 * @return array
	 */
	private function get_demo_ticket() {

		$ticket = array(
			'WooCommerceEventsDate'                    => '24-11-2020',
			'WooCommerceEventsVariations'              => '',
			'WooCommerceEventsVariationID'             => '',
			'fooevents_custom_attendee_fields_options' => 'Shirt size: L',
			'WooCommerceEventsEvent'                   => 'Event',
			'WooCommerceEventsHour'                    => '13',
			'WooCommerceEventsMinutes'                 => '00',
			'WooCommerceEventsPeriod'                  => '',
			'WooCommerceEventsHourEnd'                 => '14',
			'WooCommerceEventsMinutesEnd'              => '00',
			'WooCommerceEventsEndPeriod'               => '',
			'WooCommerceEventsLocation'                => __( 'Local Stadium', 'woocommerce-events' ),
			'WooCommerceEventsTicketLogo'              => '',
			'WooCommerceEventsTicketHeaderImage'       => '',
			'WooCommerceEventsSupportContact'          => '0841111111',
			'WooCommerceEventsTicketBackgroundColor'   => '#050505',
			'WooCommerceEventsTicketButtonColor'       => '#55AF71',
			'WooCommerceEventsTicketTextColor'         => '#FFFFFF',
			'WooCommerceEventsTicketPurchaserDetails'  => 'on',
			'WooCommerceEventsTicketAddCalendar'       => 'on',
			'WooCommerceEventsTicketDisplayDateTime'   => 'on',
			'WooCommerceEventsTicketDisplayBarcode'    => 'on',
			'WooCommerceEventsTicketText'              => 'This is preview text',
			'WooCommerceEventsDirections'              => 'These are preview directions',
			'WooCommerceEventsTicketDisplayPrice'      => 'on',
			'WooCommerceEventsTicketDisplayZoom'       => 'on',
			'WooCommerceEventsTicketType'              => 'Early Bird',
			'WooCommerceEventsProductID'               => '',
			'WooCommerceEventsTicketID'                => '111111111',
			'WooCommerceEventsOrderID'                 => '',
			'name'                                     => __( 'Preview Event', 'woocommerce-events' ),
			'cancelLink'                               => '',
			'WooCommerceEventsAttendeeName'            => '',
			'WooCommerceEventsAttendeeLastName'        => '',
			'WooCommerceEventsAttendeeTelephone'       => '',
			'WooCommerceEventsAttendeeCompany'         => '',
			'WooCommerceEventsAttendeeDesignation'     => '',
			'WooCommerceEventsAttendeeEmail'           => '',
			'customerFirstName'                        => __( 'John', 'woocommerce-events' ),
			'customerLastName'                         => __( 'Doe', 'woocommerce-events' ),
			'customerEmail'                            => '',
			'FooEventsTicketFooterText'                => '',
			'price'                                    => '$99.00',
			'WooCommerceEventsPrice'                   => '$99.00',
			'WooCommerceEventsTicketDisplayBookings'   => '',
			'WooCommerceEventsTicketDisplayMultiDay'   => '',
			'WooCommerceEventsType'                    => '',
			'TicketNumberTicketOutput'                 => '',
			'ticketNumber'                             => 1,
			'ID'                                       => 0,
			'WooCommerceEventsDisplayPoweredby'        => 'off',

		);

		return $ticket;
	}

	/**
	 * Gets a list of FooEvents Ticket themes
	 */
	public function get_ticket_themes() {

		$valid_themes = array();

		foreach ( new DirectoryIterator( $this->config->theme_packs_path ) as $file ) {

			if ( $file->isDir() && ! $file->isDot() ) {

				$theme_name = $file->getFilename();

				$theme_path = $file->getPath();
				$theme_path = $theme_path . '/' . $theme_name;

				$theme_name_pretty = str_replace( '_', ' ', $theme_name );
				$theme_name_prep   = ucwords( $theme_name_pretty );

				if ( file_exists( $theme_path . '/header.php' ) && file_exists( $theme_path . '/footer.php' ) && file_exists( $theme_path . '/ticket.php' ) ) {

					$theme_config = array();
					if ( file_exists( $theme_path . '/config.json' ) ) {

						$theme_config                             = file_get_contents( $theme_path . '/config.json' );
						$theme_config                             = json_decode( $theme_config, true );
						$valid_themes[ $theme_name_prep ]['name'] = $theme_config['name'];

					} else {

						$valid_themes[ $theme_name_prep ]['name'] = $theme_name_prep;

					}

					$valid_themes[ $theme_name_prep ]['path'] = $theme_path;
					$theme_url                                = $this->config->theme_packs_url . $theme_name;
					$valid_themes[ $theme_name_prep ]['url']  = $theme_url;

					if ( file_exists( $theme_path . '/preview.jpg' ) ) {

						$valid_themes[ $theme_name_prep ]['preview'] = $theme_url . '/preview.jpg';

					} else {

						$valid_themes[ $theme_name_prep ]['preview'] = $this->config->event_plugin_url . 'images/no-preview.jpg';

					}

					$valid_themes[ $theme_name_prep ]['file_name'] = $file->getFilename();

				}
			}
		}

		return $valid_themes;
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
