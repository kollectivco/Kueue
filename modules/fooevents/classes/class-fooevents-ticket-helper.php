<?php
/**
 * Ticket helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 *  Ticket helper class
 */
class FooEvents_Ticket_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	public $config;

	/**
	 * Barcode helper object
	 *
	 * @var object $barcode_helper
	 */
	private $barcode_helper;

	/**
	 * ICS helper object
	 *
	 * @var object $ics_helper
	 */
	private $ics_helper;

	/**
	 * Mail helper object
	 *
	 * @var object $ics_helper
	 */
	public $mail_helper;

	/**
	 * Checkout helper object
	 *
	 * @var object $checkout_helper
	 */
	public $checkout_helper;

	/**
	 * Zoom API helper object
	 *
	 * @var object $zoom_api_helper
	 */
	public $zoom_api_helper;

	/**
	 * Required_csv_fields
	 *
	 * @var array $required_csv_fields
	 */
	public $required_csv_fields;

	/**
	 * CSV field names
	 *
	 * @var array $csv_field_names
	 */
	public $csv_field_names;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;
		$this->register_ticket_post_type();

		require_once $this->config->class_path . 'class-check-in-list-table-tickets.php';

		// BarcodeHelper.
		require_once $this->config->class_path . 'class-fooevents-barcode-helper.php';
		$this->barcode_helper = new FooEvents_Barcode_Helper( $this->config );

		// ICSHelper.
		require_once $this->config->class_path . 'class-fooevents-ics-helper.php';
		$this->ics_helper = new FooEvents_ICS_Helper( $this->config );

		// MailHelper.
		require_once $this->config->class_path . 'class-fooevents-mail-helper.php';
		$this->mail_helper = new FooEvents_Mail_Helper( $this->config );

		// ZoomAPIHelper.
		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$this->zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );

		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'manage_edit-event_magic_tickets_columns', array( &$this, 'add_admin_columns' ), 10, 1 );
		add_action( 'manage_event_magic_tickets_posts_custom_column', array( &$this, 'add_admin_column_content' ), 10, 1 );
		add_action( 'pre_get_posts', array( $this, 'status_orderby' ) );
		add_action( 'add_meta_boxes', array( &$this, 'add_tickets_meta_boxes' ), 1, 2 );
		add_action( 'save_post', array( &$this, 'save_edit_ticket_meta_boxes' ), 1, 2 );
		add_action( 'save_post', array( &$this, 'save_add_ticket_meta_boxes' ), 1, 2 );
		add_action( 'template_redirect', array( $this, 'redirect_ticket' ) );
		add_action( 'post_row_actions', array( $this, 'remove_ticket_view' ), 10, 2 );
		add_action( 'parse_query', array( $this, 'filter_unpaid_tickets' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'display_bulk_resend' ) );
		add_action( 'admin_action_resend_tickets', array( $this, 'bulk_resend' ) );
		add_action( 'wp_ajax_fetch_woocommerce_variations', array( $this, 'fetch_woocommerce_variations' ) );
		add_action( 'wp_ajax_fetch_wordpress_user', array( $this, 'fetch_wordpress_user' ) );
		add_action( 'wp_ajax_fetch_capture_attendee_details', array( $this, 'fetch_capture_attendee_details' ) );
		add_action( 'wp_ajax_fooevents_validate_add_ticket', array( $this, 'validate_add_ticket' ) );
		add_action( 'wp_ajax_fooevents_validate_edit_ticket', array( $this, 'validate_edit_ticket' ) );
		add_action( 'wp_ajax_fooevents_get_users', array( $this, 'query_wordpress_users' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'disable_auto_save' ), 1 );
		add_action( 'admin_init', array( $this, 'disable_revisions' ), 1 );
		add_action( 'untrashed_post', array( $this, 'untrash_ticket' ), 1 );

		add_action( 'wp_ajax_resend_ticket', array( $this, 'resend_ticket' ) );
		add_action( 'wp_ajax_get_event_variations', array( $this, 'get_event_variations' ) );
		add_action( 'wp_ajax_get_event_details', array( $this, 'get_event_details' ) );

		add_action( 'before_delete_post', array( $this, 'delete_tickets_permanently' ), 1 );

		add_filter( 'pre_get_posts', array( &$this, 'tickets_where' ), 10, 1 );
		add_filter( 'manage_edit-event_magic_tickets_sortable_columns', array( $this, 'sortable_admin_columns' ) );
		add_filter( 'restrict_manage_posts', array( $this, 'filter_ticket_options' ) );
		add_filter( 'woocommerce_email_recipient_customer_completed_order', array( $this, 'suppress_admin_order_email_notifications' ), 20, 2 );

		$this->required_csv_fields = array( 'pid', 'fname', 'lname', 'email' );
		$this->csv_field_names     = array(
			'pid'   => 'Event ID',
			'fname' => 'Attendee First Name',
			'lname' => 'Attendee Last Name',
			'email' => 'Attendee Email Address',
		);
	}

	/**
	 * Registers the ticket post type.
	 */
	private function register_ticket_post_type() {

		$labels = array(
			'name'               => __( 'Tickets', 'woocommerce-events' ),
			'singular_name'      => __( 'Ticket', 'woocommerce-events' ),
			'add_new'            => __( 'Add New', 'woocommerce-events' ),
			'add_new_item'       => __( 'Add New Ticket', 'woocommerce-events' ),
			'edit_item'          => __( 'Edit Ticket', 'woocommerce-events' ),
			'new_item'           => __( 'New Ticket', 'woocommerce-events' ),
			'all_items'          => __( 'Tickets', 'woocommerce-events' ),
			'view_item'          => __( 'View Ticket', 'woocommerce-events' ),
			'search_items'       => __( 'Search Tickets', 'woocommerce-events' ),
			'not_found'          => __( 'No tickets found', 'woocommerce-events' ),
			'not_found_in_trash' => __( 'No tickets found in the Trash', 'woocommerce-events' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'FooEvents', 'woocommerce-events' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Event Tickets', 'woocommerce-events' ),
			'public'              => true,
			'exclude_from_search' => true,
			'menu_position'       => 5,
			'supports'            => array( 'custom-fields' ),
			'has_archive'         => true,
			'capabilities'        => array(
				'publish_posts'        => 'publish_event_magic_tickets',
				'edit_posts'           => 'edit_event_magic_tickets',
				'edit_published_posts' => 'edit_published_event_magic_tickets',
				'edit_others_posts'    => 'edit_others_event_magic_tickets',
				'delete_posts'         => 'delete_event_magic_tickets',
				'delete_others_posts'  => 'delete_others_event_magic_tickets',
				'read_private_posts'   => 'read_private_event_magic_tickets',
				'edit_post'            => 'edit_event_magic_ticket',
				'delete_post'          => 'delete_event_magic_ticket',
				'read_post'            => 'read_event_magic_ticket',
				'edit_published_post'  => 'edit_published_event_magic_ticket',
				'publish_post'         => 'publish_event_magic_ticket',
			),
			'capability_type'     => array( 'event_magic_ticket', 'event_magic_tickets' ),
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-tickets-alt',
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'show_in_menu'        => 'fooevents',
		);

		register_post_type( 'event_magic_tickets', $args );
	}

	/**
	 * Adds admin columns to the event ticket custom post type.
	 *
	 * @param array $columns columns.
	 * @return array $columns
	 */
	public function add_admin_columns( $columns ) {

		$columns['cb']           = __( 'Select', 'woocommerce-events' );
		$columns['title']        = __( 'Title', 'woocommerce-events' );
		$columns['Order']        = __( 'Order', 'woocommerce-events' );
		$columns['Event']        = __( 'Event', 'woocommerce-events' );
		$columns['Purchaser']    = __( 'Purchaser', 'woocommerce-events' );
		$columns['Attendee']     = __( 'Attendee', 'woocommerce-events' );
		$columns['PurchaseDate'] = __( 'Purchase Date', 'woocommerce-events' );

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$columns['Bookings'] = __( 'Bookings', 'woocommerce-events' );

		}

		$columns['Status'] = __( 'Status', 'woocommerce-events' );

		return $columns;
	}

	/**
	 * Adds column content to the event ticket custom post type.
	 *
	 * @param string $column column.
	 * @global object $post
	 */
	public function add_admin_column_content( $column ) {

		global $post;
		global $woocommerce;

		$order_id    = get_post_meta( $post->ID, 'WooCommerceEventsOrderID', true );
		$customer_id = get_post_meta( $post->ID, 'WooCommerceEventsCustomerID', true );
		$order       = wc_get_order( $order_id );

		switch ( $column ) {
			case 'Event':
				$woocommerce_events_product_id = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
				echo '<a href="' . esc_attr( get_site_url() ) . '/wp-admin/post.php?post=' . esc_attr( $woocommerce_events_product_id ) . '&action=edit">' . esc_attr( get_the_title( $woocommerce_events_product_id ) ) . '</a>';
				break;

			case 'Order':
				$woocommerce_events_order_id = get_post_meta( $post->ID, 'WooCommerceEventsOrderID', true );
				echo '<a href="' . esc_attr( get_site_url() ) . '/wp-admin/post.php?post=' . esc_attr( $woocommerce_events_order_id ) . '&action=edit">' . esc_attr( $woocommerce_events_order_id ) . '</a>';
				break;

			case 'Purchaser':
				if ( ! empty( $customer_id ) && ! ( $customer_id instanceof WP_Error ) ) {

					$woocommerce_events_purchaser_first_name = get_post_meta( $post->ID, 'WooCommerceEventsPurchaserFirstName', true );
					$woocommerce_events_purchaser_last_name  = get_post_meta( $post->ID, 'WooCommerceEventsPurchaserLastName', true );
					$woocommerce_events_purchaser_email      = get_post_meta( $post->ID, 'WooCommerceEventsPurchaserEmail', true );
					echo '<a href="' . esc_attr( get_site_url() ) . '/wp-admin/user-edit.php?user_id=' . esc_attr( $customer_id ) . '">' . esc_attr( $woocommerce_events_purchaser_first_name ) . ' ' . esc_attr( $woocommerce_events_purchaser_last_name ) . ' - ( ' . esc_attr( $woocommerce_events_purchaser_email ) . ' )</a>';

				} elseif ( false !== $order ) {

					// guest account.
					try {

						if ( false !== $order ) {

							$billing_first_name = $order->get_billing_first_name();
							$billing_last_name  = $order->get_billing_last_name();
							$billing_email      = $order->get_billing_email();

							if ( ! empty( $billing_first_name ) && ! empty( $billing_last_name ) ) {

								echo esc_attr( $billing_first_name ) . ' ' . esc_attr( $billing_last_name ) . ' - ( ' . esc_attr( $billing_email ) . ' )';

							} else {

								echo '-';

							}
						}
					} catch ( Exception $e ) {

						// Do nothing for now.

					}
				} elseif ( false === $order ) {

					$woocommerce_events_create_type = get_post_meta( $post->ID, 'WooCommerceEventsCreateType', true );

					if ( 'CSV' !== $woocommerce_events_create_type ) {

						echo '<i>' . esc_attr( __( 'Warning: WooCommerce order has been deleted', 'woocommerce-events' ) ) . '</i><br /><br />';

					}
				}

				break;

			case 'Attendee':
				$woocommerce_events_attendee_name      = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeName', true );
				$woocommerce_events_attendee_last_name = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeLastName', true );
				$woocommerce_events_attendee_email     = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeEmail', true );

				if ( ! empty( $woocommerce_events_attendee_name ) && ! empty( $woocommerce_events_attendee_last_name ) && ! empty( $woocommerce_events_attendee_email ) ) {

					echo esc_attr( $woocommerce_events_attendee_name ) . ' ' . esc_attr( $woocommerce_events_attendee_last_name ) . ' - ( ' . esc_attr( $woocommerce_events_attendee_email ) . ' )';

				} elseif ( ! empty( $woocommerce_events_attendee_name ) && ! empty( $woocommerce_events_attendee_last_name ) ) {

					echo esc_attr( $woocommerce_events_attendee_name ) . ' ' . esc_attr( $woocommerce_events_attendee_last_name );

				} elseif ( ! empty( $woocommerce_events_attendee_email ) ) {

					echo esc_attr( $woocommerce_events_attendee_email );

				} else {

					echo '-';

				}

				break;

			case 'PurchaseDate':
				echo esc_attr( $post->post_date );

				break;

			case 'Status':
				$woocommerce_events_product_id      = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
				$woocommerce_events_type            = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
				$woocommerce_events_num_days        = (int) get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );
				$woocommerce_events_multiday_status = '';
				$woocommerce_events_status          = get_post_meta( $post->ID, 'WooCommerceEventsStatus', true );

				if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

					require_once ABSPATH . '/wp-admin/includes/plugin.php';

				}

				if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

					$fooevents_multiday_events          = new Fooevents_Multiday_Events();
					$woocommerce_events_multiday_status = $fooevents_multiday_events->display_multiday_status_ticket_meta_all( $post->ID );

				}

				if ( 'single' === $woocommerce_events_type || 'bookings' === $woocommerce_events_type || 'seating' === $woocommerce_events_type || 'Unpaid' === $woocommerce_events_status || 'Canceled' === $woocommerce_events_status || 'Cancelled' === $woocommerce_events_status || 1 === $woocommerce_events_num_days ) {

					echo wp_kses_post( $woocommerce_events_status );

				} else {

					echo wp_kses_post( $woocommerce_events_multiday_status );

				}

				break;

			case 'Options':
				break;
		}
	}

	/**
	 * Make columns sortable
	 *
	 * @param array $columns columns.
	 * @return array
	 */
	public function sortable_admin_columns( $columns ) {

		$columns['Status']       = 'Status';
		$columns['Order']        = 'Order';
		$columns['Event']        = 'Event';
		$columns['Purchaser']    = 'Purchaser';
		$columns['Attendee']     = 'Attendee';
		$columns['PurchaseDate'] = 'PurchaseDate';
		$columns['Bookings']     = 'Bookings';

		return $columns;
	}

	/**
	 * Add admin ticket themes menu item
	 */
	public function add_menu_item() {

		add_submenu_page( 'fooevents', __( 'Import Tickets', 'woocommerce-events' ), __( 'Import Tickets', 'woocommerce-events' ), 'edit_posts', 'fooevents-import-tickets', array( $this, 'display_import_tickets_page' ) );
	}

	/**
	 * Displays the import tickets page
	 */
	public function display_import_tickets_page() {

		if ( ! isset( $_POST['step'] ) ) {

			$this->display_import_tickets_form();

		}

		if ( isset( $_POST['step'] ) ) {

			$step = (int) sanitize_text_field( wp_unslash( $_POST['step'] ) );

			if ( 1 === $step ) {

				$validate_csv_upload = $this->validate_csv_upload();

				if ( false === $validate_csv_upload[0] ) {

					$this->display_import_tickets_form( false, $validate_csv_upload[1] );

				} else {

					$this->process_import_tickets_step_one();

				}
			} elseif ( 2 === $step ) {

				$validate_mapping = $this->validate_step_one();

				if ( false === $validate_mapping[0] ) {

					// Failed validation.
					$this->process_import_tickets_step_one( false, $validate_mapping[1] );

				} else {

					$this->process_import_tickets_step_two();

				}
			} elseif ( 3 === $step ) {

				$this->process_import_tickets_step_three();

			}
		}
	}

	/**
	 * Validates the uploaded csv
	 *
	 * @return array
	 */
	public function validate_csv_upload() {

		if ( empty( $_FILES['fooevents-import-tickets-file']['tmp_name'] ) ) {

			return array( false, __( 'Please select CSV file to upload', 'woocommerce-events' ) );

		}

		$filename  = $_FILES['fooevents-import-tickets-file']['name'];
		$ext       = pathinfo( $filename, PATHINFO_EXTENSION );
		$temp_file = $_FILES['fooevents-import-tickets-file']['tmp_name'];
		$csv       = file( $temp_file );
		$num_rows  = count( $csv );

		if ( 'csv' !== $ext ) {

			return array( false, __( 'Please select CSV file to upload', 'woocommerce-events' ) );

		}

		if ( 100 < $num_rows ) {

			// return array( false, __( 'Maximum number of tickets to import is 100', 'woocommerce-events' ) );

		}

		/** Check if file finished processing */

		$finished_process_csv = $this->check_csv_finish_processing( $_FILES );

		if ( $finished_process_csv ) {

			return array( false, __( 'File has been processed. All tickets imported.', 'woocommerce-events' ) );

		}

		/** END Check if file finished processing */

		return array( true, '' );
	}

	/**
	 * Displays the import tickets form
	 *
	 * @param bool   $validation_passed did the validation pass.
	 * @param string $error_message the error message.
	 */
	public function display_import_tickets_form( $validation_passed = true, $error_message = '' ) {

		include $this->config->template_path . 'tickets-import-tickets.php';
	}

	/**
	 * Process uploaded CSV file of tickets
	 *
	 * @param bool   $validation_passed did the validation pass.
	 * @param string $error_message the error message.
	 */
	public function process_import_tickets_step_one( $validaion_passed = true, $error_message = '' ) {

		$tickets_array = '';
		$tickets_json  = '';
		if ( true === $validaion_passed ) {

			$saved_filename_md5  = get_option( 'fooevents_csv_filename_md5' );
			$saved_file_size     = get_option( 'fooevents_csv_size' );
			$saved_file_run      = get_option( 'fooevents_csv_run' );
			$saved_file_run_time = get_option( 'fooevents_csv_run_time' );
			$batch_size          = (int) get_option( 'globalWooCommerceEventsImportBatchSize' );

			if ( empty( $batch_size ) || 1 == $batch_size ) {

				$batch_size = 100;

			}

			$filename_md5 = md5( $_FILES['fooevents-import-tickets-file']['name'] );
			$file_size    = $_FILES['fooevents-import-tickets-file']['size'];
			$file_run     = $saved_file_run;

			$timestamp             = current_time( 'timestamp' );
			$twenty_four_hours_ago = $timestamp - 86400;

			if ( $saved_filename_md5 !== $filename_md5 && $saved_file_size !== $file_size || ( empty( $saved_file_run_time ) || $saved_file_run_time < $twenty_four_hours_ago ) ) {

				update_option( 'fooevents_csv_filename_md5', $filename_md5 );
				update_option( 'fooevents_csv_size', $file_size );
				update_option( 'fooevents_csv_run', 1 );

				$file_run = 1;
			}

			if ( empty( $file_run ) ) {

				$file_run = 1;

			}

			$temp_file              = $_FILES['fooevents-import-tickets-file']['tmp_name'];
			$file_name              = $_FILES['fooevents-import-tickets-file']['name'];
			$tickets_array_original = array_map( 'str_getcsv', file( $temp_file ) );
			$num_tickets_original   = count( $tickets_array_original );
			$tickets_num_to_run     = $file_run * $batch_size;
			$tickets_num_to_start   = $tickets_num_to_run - $batch_size;

			$output_num_runs = 1;

			if ( $num_tickets_original > $batch_size ) {

				$output_num_runs = ceil( $num_tickets_original / $batch_size );

			}

			if ( $file_run == 1 ) {

				$tickets_array = $this->ticket_array_slice( $tickets_array_original, 0, $batch_size );

			} else {

				$tickets_array = $this->ticket_array_slice( $tickets_array_original, $tickets_num_to_start, $batch_size );

			}
		} else {

			$tickets_array = json_decode( wp_unslash( $_POST['fooevents-import-tickets-file'] ) );
			$file_run      = sanitize_text_field( wp_unslash( $_POST['fooevents-import-tickets-run'] ) );

		}

		include $this->config->template_path . 'tickets-import-tickets-field-selection.php';
	}

	/**
	 * Validate CSV mapping
	 */
	public function validate_step_one() {

		$result = array_diff( $this->required_csv_fields, $_POST['fooevents-csv-field'] );

		if ( ! empty( $result ) ) {

			foreach ( $result as $field ) {

				// translators: Placeholder is for missing field.
				return array( false, sprintf( __( 'Please check required import fields. %s is a required field.', 'woocommerce-events' ), $this->csv_field_names[ $field ] ) );

			}
		}

		return array( true, '' );
	}

	/**
	 * Process uploaded fields map of tickets
	 */
	public function process_import_tickets_step_two() {

		$tickets_array = json_decode( wp_unslash( $_POST['fooevents-import-tickets-file'] ), true );

		$ticket_headers = $tickets_array[0];
		unset( $tickets_array[0] );

		$field_map       = array_flip( $_POST['fooevents-csv-field'] );
		$file_run        = sanitize_text_field( wp_unslash( $_POST['fooevents-import-tickets-run'] ) );
		$output_num_runs = sanitize_text_field( wp_unslash( $_POST['fooevents-import-tickets-max-run'] ) );

		$field_metas = array();
		foreach ( $field_map as $key => $position ) {

			if ( strpos( $key, 'meta-' ) === 0 ) {

				$field_metas[ $ticket_headers[ $position ] ] = $position;

			}
		}

		$custom_attendee_fields = array();
		foreach ( $field_map as $key => $position ) {

			if ( strpos( $key, 'custom-' ) === 0 ) {

				$custom_attendee_fields[ $ticket_headers[ $position ] ] = $position;

			}
		}

		$errors = array();
		include $this->config->template_path . 'tickets-import-tickets-confirmation.php';
	}

	/**
	 * Process and import tickets
	 */
	public function process_import_tickets_step_three() {

		$tickets_array         = json_decode( wp_unslash( $_POST['fooevents-import-tickets-file'] ), true );
		$meta_array            = json_decode( wp_unslash( $_POST['fooevents-csv-field-meta'] ), true );
		$custom_attendee_array = json_decode( wp_unslash( $_POST['fooevents-csv-custom-attendee'] ), true );
		$file_run              = sanitize_text_field( wp_unslash( $_POST['fooevents-import-tickets-run'] ) );
		$output_num_runs       = sanitize_text_field( wp_unslash( $_POST['fooevents-import-tickets-max-run'] ) );

		$field_map = json_decode( wp_unslash( $_POST['fooevents-csv-field'] ) );
		$field_map = array_flip( $field_map );

		$processed_tickets = array();
		$x                 = 0;
		foreach ( $tickets_array as $ticket ) {

			$processed_tickets[ $x ]['WooCommerceEventsAttendeeName']     = trim( $ticket[ $field_map['fname'] ] );
			$processed_tickets[ $x ]['WooCommerceEventsAttendeeLastName'] = trim( $ticket[ $field_map['lname'] ] );
			$processed_tickets[ $x ]['WooCommerceEventsAttendeeEmail']    = trim( $ticket[ $field_map['email'] ] );
			$processed_tickets[ $x ]['WooCommerceEventsProductID']        = trim( $ticket[ $field_map['pid'] ] );

			if ( isset( $field_map['productvariation'] ) ) {

				$variation_id  = trim( $ticket[ $field_map['productvariation'] ] );
				$variation_obj = new WC_Product_Variation( $variation_id );

				if ( ! empty( $variation_obj ) ) {

					$variations = array();

					foreach ( $variation_obj->get_variation_attributes() as $attribute => $attribute_value ) {

						$variations[ $attribute ] = (string) $attribute_value;

					}

					$processed_tickets[ $x ]['WooCommerceEventsVariationID'] = $variation_id;
					$processed_tickets[ $x ]['WooCommerceEventsVariations']  = $variations;

				}
			}

			if ( isset( $field_map['oid'] ) ) {

				$processed_tickets[ $x ]['WooCommerceEventsOrderID'] = trim( $ticket[ $field_map['oid'] ] );

			}

			if ( isset( $field_map['phone'] ) ) {

				$processed_tickets[ $x ]['WooCommerceEventsAttendeeTelephone'] = trim( $ticket[ $field_map['phone'] ] );

			}

			if ( isset( $field_map['company'] ) ) {

				$processed_tickets[ $x ]['WooCommerceEventsAttendeeCompany'] = trim( $ticket[ $field_map['company'] ] );

			}

			if ( isset( $field_map['designation'] ) ) {

				$processed_tickets[ $x ]['WooCommerceEventsAttendeeDesignation'] = trim( $ticket[ $field_map['designation'] ] );

			}

			if ( isset( $field_map['bookingdate'] ) && isset( $field_map['bookingslot'] ) ) {

				$processed_tickets[ $x ]['WooCommerceEventsBookingDateID'] = trim( $ticket[ $field_map['bookingdate'] ] );
				$processed_tickets[ $x ]['WooCommerceEventsBookingSlotID'] = trim( $ticket[ $field_map['bookingslot'] ] );

			}

			foreach ( $meta_array as $key => $position ) {

				$clean_key                                     = $this->process_clean_csv_heading( $key );
				$processed_tickets[ $x ]['meta'][ $clean_key ] = $ticket[ $position ];

			}

			foreach ( $custom_attendee_array as $key => $position ) {

				$clean_key = $this->process_clean_csv_heading( $key );
				$processed_tickets[ $x ]['custom_attendee_fields'][ $clean_key ] = $ticket[ $position ];

			}

			++$x;

		}

		if ( ! empty( $processed_tickets ) ) {

			$created_tickets = $this->create_imported_tickets( $processed_tickets );

			include $this->config->template_path . 'tickets-import-tickets-log.php';
		}
	}

	/**
	 * Create each imported ticket
	 *
	 * @param array $tickets array of CSV tickets.
	 * @return array
	 */
	public function create_imported_tickets( $tickets ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$created_tickets = array();
		$x               = 0;
		foreach ( $tickets as $ticket ) {

			$rand = rand( 111111, 999999 );

			$post = array(

				'post_author'  => 1,
				'post_content' => 'Ticket',
				'post_status'  => 'publish',
				'post_title'   => 'Assigned Ticket ' . $rand,
				'post_type'    => 'event_magic_tickets',

			);

			$post['ID']         = wp_insert_post( $post );
			$ticket_id          = $post['ID'] . $rand;
			$post['post_title'] = '#' . $ticket_id;
			$ticket_post_id     = wp_update_post( $post );
			$ticket_hash        = $this->generate_random_string( 8 );

			update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', $ticket_id );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketHash', $ticket_hash );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', trim( sanitize_text_field( $ticket['WooCommerceEventsProductID'] ) ) );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeName', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeName'] ) ) );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeLastName', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeLastName'] ) ) );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeEmail'] ) ) );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketsGenerated', 'yes' );

			$ticket_number = $this->increment_ticket_number( $ticket['WooCommerceEventsProductID'] );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketNumber', $ticket_number );

			$ticket_formated_number = $this->format_ticket_number( $ticket['WooCommerceEventsProductID'], $ticket_number );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketNumberFormatted', $ticket_formated_number );

			if ( isset( $ticket['WooCommerceEventsOrderID'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', trim( sanitize_text_field( $ticket['WooCommerceEventsOrderID'] ) ) );
			}

			if ( isset( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeTelephone', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) );
			}

			if ( isset( $ticket['WooCommerceEventsAttendeeCompany'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeCompany', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeCompany'] ) ) );
			}

			if ( isset( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeDesignation', trim( sanitize_text_field( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) );
			}

			if ( isset( $ticket['WooCommerceEventsVariations'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsVariations', $ticket['WooCommerceEventsVariations'] );
			}

			if ( isset( $ticket['WooCommerceEventsVariationID'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsVariationID', trim( sanitize_text_field( $ticket['WooCommerceEventsVariationID'] ) ) );
			}

			if ( isset( $ticket['WooCommerceEventsBookingDateID'] ) && isset( $ticket['WooCommerceEventsBookingSlotID'] ) ) {
				update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingDateID', trim( sanitize_text_field( $ticket['WooCommerceEventsBookingDateID'] ) ) );
				update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingSlotID', trim( sanitize_text_field( $ticket['WooCommerceEventsBookingSlotID'] ) ) );

				if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

					$fooevents_bookings = new FooEvents_Bookings();
					$booking_date       = $fooevents_bookings->get_date_from_ids( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingSlotID'], $ticket['WooCommerceEventsBookingDateID'] );

					update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingDate', $booking_date );

					$booking_slot_label = $fooevents_bookings->get_label_from_id( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingSlotID'] );

					update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingSlot', $booking_slot_label );

				}
			}

			// Add metas.
			if ( ! empty( $ticket['meta'] ) ) {

				foreach ( $ticket['meta'] as $key => $value ) {

					$field_name = trim( sanitize_text_field( $key ) );
					update_post_meta( $ticket_post_id, $field_name, trim( $value ) );

				}
			}

			// Add Custom Attendee Fields.
			if ( ! empty( $ticket['custom_attendee_fields'] ) ) {

				foreach ( $ticket['custom_attendee_fields'] as $key => $value ) {

					$field_name = trim( sanitize_text_field( $key ) );
					update_post_meta( $ticket_post_id, $field_name, trim( $value ) );

				}
			}

			update_post_meta( $ticket_post_id, 'WooCommerceEventsStatus', 'Not Checked In' );
			update_post_meta( $ticket_post_id, 'WooCommerceEventsCreateType', 'CSV' );

			++$x;

			$created_tickets['created_tickets'][ $x ] = array(
				'ticket_id' => $ticket_id,
				'post_id'   => $ticket_post_id,
			);

			if ( ! file_exists( $this->config->barcode_path . $ticket_hash . '-' . $ticket_id . '.png' ) ) {

				$this->barcode_helper->generate_barcode( $ticket_id, $ticket_hash );
				$this->barcode_helper->generate_barcode( $ticket['WooCommerceEventsProductID'] . '-' . $ticket_formated_number, $ticket_hash, $ticket_id, 'tn' );

			}
		}

		$saved_file_run = get_option( 'fooevents_csv_run' );

		if ( empty( $saved_file_run ) ) {

			$saved_file_run = 1;

		}

		$file_run = $saved_file_run + 1;
		update_option( 'fooevents_csv_run', $file_run );
		update_option( 'fooevents_csv_run_time', current_time( 'timestamp' ) );

		return $created_tickets;
	}

	/**
	 * Increments ticket number
	 *
	 * @param int $product_id the product ID.
	 * @return int
	 */
	public function increment_ticket_number( $product_id ) {

		$woocommerce_events_tickets_generated = get_post_meta( $product_id, '_WooCommerceEventsCounter', true );

		if ( empty( $woocommerce_events_tickets_generated ) ) {

			$woocommerce_events_tickets_generated = 1;

		} else {

			++$woocommerce_events_tickets_generated;

		}

		update_post_meta( $product_id, '_WooCommerceEventsCounter', $woocommerce_events_tickets_generated );

		return $woocommerce_events_tickets_generated;
	}

	/**
	 * Returns formatted ticket number based on event options
	 *
	 * @param int    $product_id the product id.
	 * @param string $ticket_number the ticket number.
	 * @return string
	 */
	public function format_ticket_number( $product_id, $ticket_number ) {

		$padding = (int) get_post_meta( $product_id, 'WooCommerceEventsTicketNumberOutput', true );
		$prefix  = get_post_meta( $product_id, 'WooCommerceEventsTicketNumberPrefix', true );
		$suffix  = get_post_meta( $product_id, 'WooCommerceEventsTicketNumberSuffix', true );

		$identifier = str_pad( $ticket_number, $padding, '0', STR_PAD_LEFT );

		$identifier = $prefix . $identifier . $suffix;

		return $identifier;
	}

	/**
	 * Make the status field sortable
	 *
	 * @param object $query query.
	 * @return object
	 */
	public function status_orderby( $query ) {

		if ( ! is_admin() ) {

			return;

		}

		if ( in_array( $query->get( 'post_type' ), array( 'event_magic_tickets' ), true ) ) {

			if ( isset( $_GET['event_id'] ) ) {

				$event_id = sanitize_text_field( $_GET['event_id'] );

				$query->set(
					'meta_query',
					array(
						array(
							'key'     => 'WooCommerceEventsProductID',
							'value'   => $event_id,
							'compare' => '=',
						),
					)
				);

			}

			if ( isset( $_GET['orderby'] ) ) {

				$orderby = sanitize_text_field( $_GET['orderby'] );

				if ( 'Status' === $orderby ) {

					$query->set( 'meta_key', 'WooCommerceEventsStatus' );
					$query->set( 'orderby', 'meta_value' );
					$query->set( 'meta_value', '' );

				}

				if ( 'Attendee' === $orderby ) {

					$query->set( 'meta_key', 'WooCommerceEventsAttendeeName' );
					$query->set( 'orderby', 'meta_value' );
					$query->set( 'meta_value', '' );

				}

				if ( 'Purchaser' === $orderby ) {

					$query->set( 'meta_key', 'WooCommerceEventsPurchaserFirstName' );
					$query->set( 'orderby', 'meta_value' );
					$query->set( 'meta_value', '' );

				}

				if ( 'Event' === $orderby ) {

					$query->set( 'meta_key', 'WooCommerceEventsProductName' );
					$query->set( 'orderby', 'meta_value' );
					$query->set( 'meta_value', '' );

				}
			}
		}

		return $query;
	}

	/**
	 * Adds meta boxes to the tickets custom post type page.
	 */
	public function add_tickets_meta_boxes() {

		$screens = array( 'event_magic_tickets' );

		foreach ( $screens as $screen ) {

			if ( isset( $_GET['post'] ) ) {

				add_meta_box(
					'woocommerce_events_ticket_details',
					__( 'Ticket Details', 'woocommerce-events' ),
					array( &$this, 'edit_ticket_admin' ),
					$screen,
					'normal',
					'high'
				);

				add_meta_box(
					'woocommerce_events_ticket_check_in_log',
					__( 'Access Log', 'woocommerce-events' ),
					array( &$this, 'display_check_in_list' ),
					$screen,
					'normal',
					'high'
				);

				add_meta_box(
					'woocommerce_events_ticket_status',
					__( 'Ticket Status', 'woocommerce-events' ),
					array( &$this, 'edit_tickets_status_box' ),
					$screen,
					'side',
					'default'
				);

				add_meta_box(
					'woocommerce_events_ticket_resend_ticket',
					__( 'Resend Ticket', 'woocommerce-events' ),
					array( &$this, 'edit_tickets_resend_tickets_box' ),
					$screen,
					'side',
					'low'
				);

			}

			if ( ! isset( $_GET['post'] ) ) {

				add_meta_box(
					'woocommerce_events_ticket_add_event',
					__( 'Event', 'woocommerce-events' ),
					array( &$this, 'add_ticket_admin' ),
					$screen,
					'normal',
					'high'
				);

			}
		}
	}

	/**
	 * Displays manual add ticket form.
	 */
	public function add_ticket_admin() {

		$events = new WP_Query(
			array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsEvent',
						'value' => 'Event',
					),
				),
			)
		);
		$events = $events->get_posts();

		$admin_users = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'display_name', 'user_email' ),
			)
		);

		$users = get_users(
			array(
				'role'     => 'customer',
				'number'   => 15000,
				'orderby'  => 'meta_value',
				'order'    => 'DESC',
				'meta_key' => 'wc_last_active',
				'fields'   => array( 'ID', 'display_name', 'user_email' ),
			)
		);

		$users = array_merge( $admin_users, $users );

		require $this->config->template_path . 'tickets-add-ticket.php';
	}

	/**
	 * Displays edit/details ticket page meta box
	 *
	 * @global object $post
	 * @global object $woocommerce
	 * @global object $wpdb
	 */
	public function edit_ticket_admin() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		global $post;

		$order_id    = get_post_meta( $post->ID, 'WooCommerceEventsOrderID', true );
		$customer_id = get_post_meta( $post->ID, 'WooCommerceEventsCustomerID', true );

		$ticket = $this->get_ticket_data( $post->ID, 'admin' );

		$barcode_url        = $this->config->barcode_url;
		$barcode_identifier = '';

		if ( 'ticketnumberformatted' == $ticket['WooCommerceEventsTicketIdentifierOutput'] ) {

			$barcode_identifier = '-tn';

		}

		$booking_options                   = '';
		$woocommerce_events_booking_fields = array();
		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$fooevents_bookings = new FooEvents_Bookings();
			$booking_options    = $fooevents_bookings->ticket_details_booking_fields( $post->ID, $ticket['WooCommerceEventsProductID'] );

			$woocommerce_events_booking_slot_id = get_post_meta( $post->ID, 'WooCommerceEventsBookingSlotID', true );
			$woocommerce_events_booking_date_id = get_post_meta( $post->ID, 'WooCommerceEventsBookingDateID', true );

			$woocommerce_events_booking_fields = array(
				'slot_id' => $woocommerce_events_booking_slot_id,
				'date_id' => $woocommerce_events_booking_date_id,
			);

		}

		$custom_attendee_options = '';
		$seating_options         = '';
		$pdf_ticket_link         = '';

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$fooevents_multiday_events          = new Fooevents_Multiday_Events();
			$woocommerce_events_multiday_status = $fooevents_multiday_events->display_multiday_status_ticket_meta_all( $post->ID );

		}

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
			$custom_attendee_options          = $fooevents_custom_attendee_fields->ticket_details_attendee_fields( $post->ID, $ticket['WooCommerceEventsProductID'] );

		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating = new Fooevents_Seating();
			$seating_options   = $fooevents_seating->ticket_details_seating_fields( $ticket['WooCommerceEventsProductID'], $post->ID );

		}

		if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

			$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();
			$pdf_ticket_link       = $fooevents_pdf_tickets->display_ticket_download( $post->ID, $this->config->barcode_path, $this->config->event_plugin_url );

		}

		$woocommerce_events_product_id = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );

		$ticket_text_options = array_merge( array( 'WooCommerceEventsProductID' => $woocommerce_events_product_id ), $woocommerce_events_booking_fields );

		$ticket['WooCommerceEventsZoomText'] = $this->zoom_api_helper->get_ticket_text( $ticket_text_options, 'admin' );

		require $this->config->template_path . 'tickets-edit-ticket.php';
	}

	/**
	 * Get ticket check-in times
	 *
	 * @param int $id ID.
	 * @return array
	 */
	public function get_checkin_times( $id ) {

		global $wpdb;

		$id = intval( $id );

		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$formated_check_in_times = array();

		$times = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE tid = %d",
				$id
			)
		);

		foreach ( $times as $time ) {

			$formated_check_in_times[ $time->day ] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' (P)', $time->checkin );

		}

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			return $formated_check_in_times;

		} elseif ( ! empty( $formated_check_in_times ) ) {

			$returned_formated_check_in_times[1] = $formated_check_in_times[1];

			return $returned_formated_check_in_times;

		} else {

			return array();

		}
	}

	/**
	 * Add ticket status meta box.
	 *
	 * @global object $post
	 */
	public function edit_tickets_status_box() {

		global $post;

		$woocommerce_events_status          = get_post_meta( $post->ID, 'WooCommerceEventsStatus', true );
		$woocommerce_events_product_id      = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
		$woocommerce_events_type            = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
		$woocommerce_events_num_days        = (int) get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );
		$woocommerce_events_multiday_status = '';

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) && $woocommerce_events_num_days > 1 && ( 'sequential' === $woocommerce_events_type || 'select' === $woocommerce_events_type ) ) {

			$fooevents_multiday_events          = new Fooevents_Multiday_Events();
			$woocommerce_events_multiday_status = $fooevents_multiday_events->display_multiday_status_ticket_form_meta( $post->ID );

		}

		require $this->config->template_path . 'tickets-edit-ticket-status.php';
	}

	/**
	 * Add resend ticket box
	 *
	 * @global object $post
	 * @global object $woocommerce
	 */
	public function edit_tickets_resend_tickets_box() {

		global $post;
		global $woocommerce;

		$order_id         = get_post_meta( $post->ID, 'WooCommerceEventsOrderID', true );
		$customer_id      = get_post_meta( $post->ID, 'WooCommerceEventsCustomerID', true );
		$event_id         = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
		$send_to_attendee = get_post_meta( $event_id, 'WooCommerceEventsEmailAttendee', true );

		$order = wc_get_order( $order_id );

		$purchaser      = array();
		$attendee_email = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeEmail', true );

		if ( 'on' === $send_to_attendee && ! empty( $attendee_email ) ) {

			$purchaser['customerEmail'] = $attendee_email;

		} elseif ( false !== $order ) {

				$purchaser['customerEmail'] = $order->get_billing_email();

		} else {

			$purchaser['customerEmail'] = $attendee_email;
		}

		if ( empty( $purchaser['customerEmail'] ) ) {

			$purchaser['customerEmail'] = $attendee_email;

		}

		require $this->config->template_path . 'tickets-edit-ticket-resend-ticket.php';
	}

	/**
	 * Saves tickets meta box settings
	 *
	 * @param int $post_id post ID.
	 * @global object $post
	 * @global object $woocommerce
	 */
	public function save_edit_ticket_meta_boxes( $post_id ) {

		global $post;
		global $woocommerce;
		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		if ( is_object( $post ) && isset( $_POST ) && empty( $_POST['add_ticket'] ) && isset( $_POST['fooevents_validation'] ) && 'true' === $_POST['fooevents_validation'] ) {

			if ( 'event_magic_tickets' === $post->post_type ) {

				$update_zoom = false;
				$zoom_args   = array( 'fooevents_ticket_id' => $post_id );

				if ( isset( $_POST['WooCommerceEventsAttendeeName'] ) ) {

					$current_woocommerce_events_attendee_name = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeName', true );

					$woocommerce_events_attendee_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeName'] ) );

					$zoom_args['first_name'] = $woocommerce_events_attendee_name;

					if ( $woocommerce_events_attendee_name !== $current_woocommerce_events_attendee_name ) {
						$update_zoom = true;
					}

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeName', $woocommerce_events_attendee_name );
				}

				if ( isset( $_POST['WooCommerceEventsAttendeeLastName'] ) ) {

					$current_woocommerce_events_attendee_last_name = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeLastName', true );

					$woocommerce_events_attendee_last_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeLastName'] ) );

					$zoom_args['last_name'] = $woocommerce_events_attendee_last_name;

					if ( $woocommerce_events_attendee_last_name !== $current_woocommerce_events_attendee_last_name ) {
						$update_zoom = true;
					}

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeLastName', $woocommerce_events_attendee_last_name );
				}

				if ( isset( $_POST['WooCommerceEventsAttendeeEmail'] ) ) {

					$current_woocommerce_events_attendee_email = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeEmail', true );

					$woocommerce_events_attendee_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeEmail'] ) );

					$zoom_args['email'] = $woocommerce_events_attendee_email;

					if ( $woocommerce_events_attendee_email !== $current_woocommerce_events_attendee_email ) {
						$this->zoom_api_helper->cancel_zoom_registrations( array( $post ) );

						$update_zoom = true;
					}

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeEmail', $woocommerce_events_attendee_email );
				}

				if ( isset( $_POST['WooCommerceEventsAttendeeTelephone'] ) ) {

					$woocommerce_events_attendee_telephone = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeTelephone'] ) );
					update_post_meta( $post_id, 'WooCommerceEventsAttendeeTelephone', $woocommerce_events_attendee_telephone );

				}

				if ( isset( $_POST['WooCommerceEventsAttendeeCompany'] ) ) {

					$woocommerce_events_attendee_company = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeCompany'] ) );
					update_post_meta( $post_id, 'WooCommerceEventsAttendeeCompany', $woocommerce_events_attendee_company );

				}

				if ( isset( $_POST['WooCommerceEventsAttendeeDesignation'] ) ) {

					$woocommerce_events_attendee_designation = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeDesignation'] ) );
					update_post_meta( $post_id, 'WooCommerceEventsAttendeeDesignation', $woocommerce_events_attendee_designation );

				}

				$woocommerce_events_product_id = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
				$woocommerce_events_num_days   = (int) get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );

				if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

					require_once ABSPATH . '/wp-admin/includes/plugin.php';

				}

				if ( ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) && $woocommerce_events_num_days > 1 ) {

					$fooevents_multiday_events          = new Fooevents_Multiday_Events();
					$woocommerce_events_multiday_status = $fooevents_multiday_events->capture_multiday_status_ticket_meta( $post_id );

				} elseif ( isset( $_POST['WooCommerceEventsStatus'] ) ) {

						$old_status = get_post_meta( $post_id, 'WooCommerceEventsStatus', true );

					if ( $old_status !== $_POST['WooCommerceEventsStatus'] ) {

						$new_status = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsStatus'] ) );
						$timestamp  = current_time( 'timestamp' );

						$wpdb->insert(
							$table_name,
							array(
								'tid'     => $post->ID,
								'eid'     => $woocommerce_events_product_id,
								'day'     => 1,
								'uid'     => get_current_user_id(),
								'status'  => $new_status,
								'checkin' => $timestamp,
							)
						);

						if ( 'Checked In' === $new_status ) {

							do_action( 'fooevents_check_in_ticket', array( $post->ID, $new_status, $timestamp ) );

						}
					}
				}

				if ( isset( $_POST ) && isset( $_POST['ticket_status'] ) && 'true' === $_POST['ticket_status'] && isset( $_POST['WooCommerceEventsStatus'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsStatus', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsStatus'] ) ) );

				}

				/* Update AddToWallet Pass info */
				$fooevents_class = new FooEvents();
				$fooevents_class->fooevents_wallet_pass( $post_id );

				if ( $update_zoom ) {
					$zoom_meeting_id = $this->zoom_api_helper->get_zoom_id_for_ticket( $post_id );

					$zoom_meeting_ids = explode( ',', $zoom_meeting_id );

					if ( ! empty( $zoom_meeting_ids ) ) {

						foreach ( $zoom_meeting_ids as $zoom_id ) {
							if ( '' !== $zoom_id ) {
								$this->zoom_api_helper->add_update_single_zoom_registrant( $zoom_id, $zoom_args );
							}
						}
					}
				}

				if ( ! empty( $_POST['WooCommerceEventsResendTicket'] ) && ! empty( $_POST['WooCommerceEventsResendTicketEmail'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$this->resend_ticket( $post->ID );

				}
			}
		}
	}

	/**
	 * Display bulk ticket resend option.
	 */
	public function display_bulk_resend() {

		global $post_type;

		if ( 'event_magic_tickets' === $post_type ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('resend_tickets').text('<?php esc_attr_e( 'Resend Tickets', 'woocommerce-events' ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('resend_tickets').text('<?php esc_attr_e( 'Resend Tickets', 'woocommerce-events' ); ?>').appendTo("select[name='action2']");
			});
			</script>
			<?php
		}
	}

	/**
	 * Bulk resend tickets.
	 */
	public function bulk_resend() {

		if ( isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) ) {

			$tickets = $_REQUEST['post'];

			foreach ( $tickets as $ticket ) {

				$this->resend_ticket( $ticket );

			}
		}
	}

	/**
	 * Redirects tickets custom most type
	 */
	public function redirect_ticket() {

		$queried_post_type = get_query_var( 'post_type' );
		if ( is_single() && 'event_magic_tickets' === $queried_post_type ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}

	/**
	 * Removes view link
	 *
	 * @param array  $action action.
	 * @param object $post post.
	 */
	public function remove_ticket_view( $action, $post ) {

		if ( 'event_magic_tickets' === $post->post_type ) {

			unset( $action['view'] );

		}

		return $action;
	}

	/**
	 * Removes unpaid tickets from the ticket list
	 *
	 * @param object $query query.
	 */
	public function filter_unpaid_tickets( $query ) {

		// if( is_admin() AND $query->query['post_type'] == 'event_magic_tickets' ) {

			/*
			$query->query_vars['meta_key']      = 'WooCommerceEventsStatus';
			$query->query_vars['meta_value']    = 'Unpaid';
			$query->query_vars['meta_compare']  = '!=';*/

		// }

		return $query;
	}

	/**
	 * Searches for post meta
	 *
	 * @param object $query query.
	 */
	public function tickets_where( $query ) {

		if ( ! is_admin() ) {

			return;

		}

		if ( in_array( $query->get( 'post_type' ), array( 'event_magic_tickets' ), true ) ) {

			$custom_fields = array(
				'WooCommerceEventsAttendeeName',
				'WooCommerceEventsAttendeeLastName',
				'WooCommerceEventsAttendeeEmail',
				'WooCommerceEventsCustomerID',
				'WooCommerceEventsVariations',
				'WooCommerceEventsPurchaserFirstName',
				'WooCommerceEventsPurchaserLastName',
				'WooCommerceEventsPurchaserEmail',
				'WooCommerceEventsStatus',
				'WooCommerceEventsTicketID',
				'WooCommerceEventsOrderID',
				'WooCommerceEventsProductName',
				'WooCommerceEventsTicketNumberFormatted',
			);

			$global_woocommerce_events_hide_unpaid_tickets = get_option( 'globalWooCommerceEventsHideUnpaidTickets', true );

			$meta_query = array( 'relation' => 'AND' );
			array_push(
				$meta_query,
				array(
					'key'     => 'WooCommerceEventsStatus',
					'value'   => '',
					'compare' => '!=',
				)
			);

			if ( 'yes' === $global_woocommerce_events_hide_unpaid_tickets ) {

				array_push(
					$meta_query,
					array(
						'key'     => 'WooCommerceEventsStatus',
						'value'   => 'Unpaid',
						'compare' => '!=',
					)
				);

			}

			if ( empty( $query->query_vars['s'] ) && isset( $_GET['s'] ) ) {

				$query->query_vars['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );

			}

			$query->set( 'meta_query', $meta_query );

			$searchterm = $query->query_vars['s'];

			$query->query_vars['s'] = '';

			if ( '' !== $searchterm ) {
				$meta_query = array( 'relation' => 'OR' );
				foreach ( $custom_fields as $cf ) {
					array_push(
						$meta_query,
						array(
							'key'     => $cf,
							'value'   => $searchterm,
							'compare' => 'LIKE',
						)
					);
				}

				$query->set( 'meta_query', $meta_query );
			}
		}

		return $query;
	}

	/**
	 * Fetch WooCommerce variations for manual add ticket
	 */
	public function fetch_woocommerce_variations() {

		global $woocommerce;

		if ( ! empty( $_POST['productID'] ) ) {

			$product_id = sanitize_text_field( wp_unslash( $_POST['productID'] ) );
			$product    = wc_get_product( $product_id );

			$variations = '';
			if ( $product && $product->is_type( 'variable' ) ) {

				$variations = $product->get_available_variations();

			}

			if ( ! empty( $variations ) ) {

				echo '<h2>Variations</h2>';
				echo '<p class="form-field">';
				echo '<label>Variation: </label>';
				echo '<select id="WooCommerceEventsSelectedVariation" name="WooCommerceEventsSelectedVariation">';

				foreach ( $variations as $variation ) {

					echo '<option value="' . esc_attr( $variation['variation_id'] ) . '">';

					foreach ( $variation['attributes'] as $attribute_type => $attribute ) {

						$variation_name_output = str_replace( 'attribute_', '', $attribute_type );
						$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
						$variation_name_output = str_replace( '_', ' ', $variation_name_output );
						$variation_name_output = str_replace( '-', ' ', $variation_name_output );
						$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
						$variation_name_output = ucwords( $variation_name_output );
						echo esc_attr( $variation_name_output ) . ': ' . esc_attr( $attribute ) . ' ';

					}

					echo '</option>';

				}

				echo '</select>';
				echo '</p>';

			}
		}

		exit();
	}

	/**
	 * Fetch WooCommerce user for manual add ticket
	 */
	public function fetch_wordpress_user() {

		global $woocommerce;
		$current_user = wp_get_current_user();

		if ( current_user_can( 'publish_event_magic_tickets' ) ) {

			if ( ! empty( $_POST['userID'] ) ) {

				$return_user = array();
				$user        = get_user_by( 'id', sanitize_text_field( wp_unslash( $_POST['userID'] ) ) );

				$return_user['ID']           = $user->ID;
				$return_user['user_login']   = $user->data->user_login;
				$return_user['display_name'] = $user->data->display_name;
				$return_user['first_name']   = get_user_meta( $user->ID, 'first_name', true );
				$return_user['last_name']    = get_user_meta( $user->ID, 'last_name', true );
				$return_user['user_email']   = $user->data->user_email;

				echo wp_json_encode( $return_user );

			}
		}

		exit();
	}

	/**
	 * Query users for add ticket page
	 */
	public function query_wordpress_users() {

		$nonce = '';
		if ( isset( $_GET['security'] ) ) {
				$nonce = esc_attr( sanitize_text_field( wp_unslash( $_GET['security'] ) ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents_add_ticket_page' ) ) {
				die( esc_attr__( 'Security check failed - FooEvents 0001', 'woocommerce-events' ) );
		}

		if ( isset( $_GET['q'] ) && ! empty( $_GET['q'] ) ) {

			$query = sanitize_text_field( wp_unslash( $_GET['q'] ) );

			$returned_users = array();
			if ( ! empty( $query ) ) {

				$users = get_users( array( 'search' => '*' . $query . '*' ) );

				foreach ( $users as $num => $user ) {

					$returned_users[ $user->data->ID ] = $user->data->display_name . ' [' . $user->data->ID . ']';

				}
			}

			echo wp_json_encode( $returned_users );

		}

		exit();
	}

	/**
	 * Fetch WooCommerce attendee for manual add ticket
	 */
	public function fetch_capture_attendee_details() {

		if ( isset( $_POST['productID'] ) ) {

			$woocommerce_events_capture_attendee_details = get_post_meta( sanitize_text_field( wp_unslash( $_POST['productID'] ) ), 'WooCommerceEventsCaptureAttendeeDetails', true );

			echo wp_json_encode( array( 'capture' => $woocommerce_events_capture_attendee_details ) );

		}
		exit();
	}

	/**
	 * Validates edit ticket data
	 */
	public function validate_edit_ticket() {

		if ( isset( $_POST['fields'] ) ) {

			parse_str( $_POST['fields'], $fields );

			if ( empty( $fields['WooCommerceEventsAttendeeName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Attendee first name is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsAttendeeLastName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Attendee last name is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsAttendeeEmail'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Purchaser email is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				if ( isset( $fields['WooCommerceEventsBookingSlotID'] ) && isset( $fields['WooCommerceEventsBookingDateID'] ) ) {

					$fooevents_bookings = new FooEvents_Bookings();

					$bookings_validation = $fooevents_bookings->admin_edit_bookings_validate( $fields['WooCommerceEventsBookingSlotID'], $fields['WooCommerceEventsBookingDateID'], $fields['fooevents_ticket_raw_id'], $fields['fooevents_event_id'] );
					$bookings_validation = json_decode( $bookings_validation, true );

					if ( 'error' === $bookings_validation['type'] ) {

						echo wp_json_encode(
							array(
								'type'    => 'error',
								'message' => $bookings_validation['message'],
							)
						);
						exit();

					}
				}
			}

			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

				$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();

				$custom_fields_validation = $fooevents_custom_attendee_fields->admin_edit_ticket_custom_fields_validate( $fields['fooevents_event_id'], $fields );
				$custom_fields_validation = json_decode( $custom_fields_validation, true );

				if ( 'error' === $custom_fields_validation['type'] ) {

					echo wp_json_encode(
						array(
							'type'    => 'error',
							'message' => $custom_fields_validation['message'],
						)
					);
					exit();

				}
			}
		}

		exit();
	}

	/**
	 * Validates add ticket data
	 */
	public function validate_add_ticket() {

		if ( isset( $_POST['fields'] ) ) {

			parse_str( $_POST['fields'], $fields );

			if ( empty( $fields['WooCommerceEventsEvent'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Event is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsPurchaserUserName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Purchaser username is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsPurchaserEmail'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Purchaser email is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsPurchaserDisplayName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Purchaser display name is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsAttendeeName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Attendee first name is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsAttendeeLastName'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Attendee last name is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsAttendeeEmail'] ) ) {

				echo wp_json_encode(
					array(
						'status'  => 'error',
						'message' => __(
							'Attendee email is required.',
							'woocommerce-events'
						),
					)
				);
				exit();

			}

			if ( empty( $fields['WooCommerceEventsClientID'] ) || 0 === $fields['WooCommerceEventsClientID'] ) {

				$usernames   = $this->get_usernames();
				$user_emails = $this->get_user_emails();

				if ( in_array( $fields['WooCommerceEventsPurchaserEmail'], $user_emails, true ) ) {

					echo wp_json_encode(
						array(
							'type'    => 'error',
							'message' => __(
								'User email address already exists.',
								'woocommerce-events'
							),
						)
					);
					exit();

				}

				if ( in_array( $fields['WooCommerceEventsPurchaserUserName'], $usernames, true ) ) {

					echo wp_json_encode(
						array(
							'type'    => 'error',
							'message' => __(
								'User display name already exists.',
								'woocommerce-events'
							),
						)
					);
					exit();

				}
			}

			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				if ( isset( $fields['WooCommerceEventsBookingSlotID'] ) && isset( $fields['WooCommerceEventsBookingDateID'] ) ) {

					$fooevents_bookings = new FooEvents_Bookings();

					$bookings_validation = $fooevents_bookings->admin_add_ticket_bookings_validate( $fields['WooCommerceEventsEvent'], $fields['WooCommerceEventsBookingSlotID'], $fields['WooCommerceEventsBookingDateID'] );
					$bookings_validation = json_decode( $bookings_validation, true );

					if ( 'error' === $bookings_validation['type'] ) {

						echo wp_json_encode(
							array(
								'type'    => 'error',
								'message' => $bookings_validation['message'],
							)
						);
						exit();

					}
				}
			}

			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

				$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();

				$custom_fields_validation = $fooevents_custom_attendee_fields->admin_add_ticket_custom_fields_validate( $fields['WooCommerceEventsEvent'], $fields );
				$custom_fields_validation = json_decode( $custom_fields_validation, true );

				if ( 'error' === $custom_fields_validation['type'] ) {

					echo wp_json_encode(
						array(
							'type'    => 'error',
							'message' => $custom_fields_validation['message'],
						)
					);
					exit();

				}
			}
		}

		exit();
	}

	/**
	 * Save manual add ticket
	 *
	 * @param int $post_id post ID.
	 */
	public function save_add_ticket_meta_boxes( $post_id ) {

		global $post;
		global $woocommerce;

		if ( ! empty( $_POST['add_ticket'] ) && ! empty( $_POST['fooevents_validation'] ) && 'true' === $_POST['fooevents_validation'] && is_object( $post ) && 'event_magic_tickets' === $post->post_type ) {

			if ( wp_doing_ajax() || wp_doing_cron() || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {

				return;

			}

			wp_dequeue_script( 'autosave' );
			$user_id = '';

			// Create new user.
			if ( empty( $_POST['WooCommerceEventsClientID'] ) && ! empty( $_POST['WooCommerceEventsPurchaserUserName'] ) && ! empty( $_POST['WooCommerceEventsPurchaserEmail'] ) ) {

				$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
				$user_id         = wp_create_user( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPurchaserUserName'] ) ), $random_password, sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPurchaserEmail'] ) ) );

				if ( $user_id instanceof WP_Error ) {

					if ( array_key_exists( 'existing_user_email', $user_id->errors ) ) {

						wp_safe_redirect( 'edit.php?post_type=event_magic_tickets&fooevents_error=3' );

					} else {

						wp_safe_redirect( 'edit.php?post_type=event_magic_tickets&fooevents_error=2' );

					}

					exit();

				}

				if ( ! empty( $user_id ) && ! empty( $_POST['WooCommerceEventsPurchaserFirstName'] ) ) {

					wp_update_user(
						array(
							'ID'           => $user_id,
							'first_name'   => sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPurchaserFirstName'] ) ),
							'last_name'    => sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPurchaserLastName'] ) ),
							'display_name' => sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPurchaserDisplayName'] ) ),
						)
					);
					wp_update_user(
						array(
							'ID'   => $user_id,
							'role' => 'Customer',
						)
					);

				}
			} elseif ( ! empty( $_POST['WooCommerceEventsClientID'] ) ) {

					$user_id = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsClientID'] ) );
			}

			// Create ticket.
			if ( ! empty( $user_id ) ) {

				$billing_first_name = get_user_meta( $user_id, 'billing_first_name', true );
				$billing_last_name  = get_user_meta( $user_id, 'billing_last_name', true );
				$billing_company    = get_user_meta( $user_id, 'billing_company', true );
				$billing_address_1  = get_user_meta( $user_id, 'billing_address_1', true );
				$billing_address_2  = get_user_meta( $user_id, 'billing_address_2', true );
				$billing_city       = get_user_meta( $user_id, 'billing_city', true );
				$billing_postcode   = get_user_meta( $user_id, 'billing_postcode', true );
				$billing_country    = get_user_meta( $user_id, 'billing_country', true );
				$billing_state      = get_user_meta( $user_id, 'billing_state', true );
				$billing_phone      = get_user_meta( $user_id, 'billing_phone', true );
				$billing_email      = get_user_meta( $user_id, 'billing_email', true );

				if ( empty( $billing_first_name ) ) {

					$billing_first_name = get_user_meta( $user_id, 'first_name', true );

				}

				if ( empty( $billing_last_name ) ) {

					$billing_last_name = get_user_meta( $user_id, 'last_name', true );

				}

				$address = array(
					'first_name' => $billing_first_name,
					'last_name'  => $billing_last_name,
					'company'    => $billing_company,
					'email'      => $billing_email,
					'phone'      => $billing_phone,
					'address_1'  => $billing_address_1,
					'address_2'  => $billing_address_2,
					'city'       => $billing_city,
					'state'      => $billing_state,
					'postcode'   => $billing_postcode,
					'country'    => $billing_country,
				);

				$product_variation = '';
				$price             = '';

				if ( ! empty( $_POST['WooCommerceEventsSelectedVariation'] ) ) {

					$product_variation = new WC_Product_Variation( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectedVariation'] ) ) );
					$price             = wc_price( $product_variation->get_price() );

				}

				$product_details = array();
				$variations      = array();
				$x               = 0;

				if ( ! empty( $product_variation ) ) {

					foreach ( $product_variation->get_variation_attributes() as $attribute => $attribute_value ) {

						$product_details['variation'][ $attribute ] = $attribute_value;
						$variations[ $attribute ]                   = (string) $attribute_value;
						++$x;

					}
				}

				$product = '';
				if ( ! empty( $_POST['WooCommerceEventsSelectedVariation'] ) ) {

					$product = new WC_Product_Variation( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectedVariation'] ) ) );

				} elseif ( ! empty( $_POST['WooCommerceEventsEvent'] ) ) {

						$product = new WC_Product( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) ) );
				}

				remove_action( 'save_post', array( &$this, 'save_add_ticket_meta_boxes' ), 1, 2 );

				$order = wc_create_order();
				$order->add_product( $product, 1, $product_details );
				$order->set_customer_id( $user_id );
				$order->calculate_totals();

				if ( ! empty( $billing_first_name ) && ! empty( $billing_last_name ) ) {

					$order->set_address( $address, 'billing' );
					$order->set_address( $address, 'shipping' );

				}

				$order_id = $order->get_id();

				$order->update_meta_data( 'WooCommerceEventsOrderAdminAddTicket', 'yes' );
				$order->update_meta_data( 'WooCommerceEventsTicketsGenerated', 'yes' );

				$order->update_status( 'completed', '', false );

				$post_ticket = array(

					'ID'           => $post_id,
					'post_author'  => $user_id,
					'post_content' => 'Ticket',
					'post_status'  => 'publish',
					'post_title'   => 'Assigned Ticket',
					'post_type'    => 'event_magic_tickets',

				);

				$user                      = get_user_by( 'id', $user_id );
				$rand                      = wp_rand( 111111, 999999 );
				$ticket_id                 = $post_id . $rand;
				$post_ticket['post_title'] = '#' . $ticket_id;
				$post_id                   = wp_update_post( $post_ticket );

				if ( empty( $price ) ) {

					$product = wc_get_product( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) ) );
					$price   = $product->get_price();
					$price   = wc_price( $price );

				}

				// ticket fields.
				update_post_meta( $post_id, 'WooCommerceEventsCustomerID', $user_id );

				if ( ! empty( $_POST['WooCommerceEventsEvent'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsProductID', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) ) );

				}

				update_post_meta( $post_id, 'WooCommerceEventsOrderID', $order_id );
				update_post_meta( $post_id, 'WooCommerceEventsTicketID', $ticket_id );
				update_post_meta( $post_id, 'WooCommerceEventsStatus', 'Not Checked In' );

				if ( ! empty( $_POST['WooCommerceEventsAttendeeName'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeName', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeName'] ) ) );

				}

				if ( ! empty( $_POST['WooCommerceEventsAttendeeLastName'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeLastName', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeLastName'] ) ) );

				}

				if ( ! empty( $_POST['WooCommerceEventsAttendeeEmail'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeEmail', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeEmail'] ) ) );

				}

				if ( ! empty( $_POST['WooCommerceEventsAttendeeTelephone'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeTelephone', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeTelephone'] ) ) );

				}

				if ( ! empty( $_POST['WooCommerceEventsAttendeeCompany'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeCompany', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeCompany'] ) ) );

				}
				if ( ! empty( $_POST['WooCommerceEventsAttendeeDesignation'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsAttendeeDesignation', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeDesignation'] ) ) );

				}

				update_post_meta( $post_id, 'WooCommerceEventsPurchaserFirstName', $user->data->display_name );
				update_post_meta( $post_id, 'WooCommerceEventsPurchaserLastName', '' );
				update_post_meta( $post_id, 'WooCommerceEventsPurchaserEmail', $user->data->user_email );

				if ( ! empty( $_POST['WooCommerceEventsSelectedVariation'] ) ) {

					update_post_meta( $post_id, 'WooCommerceEventsVariationID', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectedVariation'] ) ) );

				}

				update_post_meta( $post_id, 'WooCommerceEventsVariations', $variations );
				update_post_meta( $post_id, 'WooCommerceEventsPrice', $price );

				$ticket_hash = $this->generate_random_string( 8 );
				update_post_meta( $post_id, 'WooCommerceEventsTicketHash', $ticket_hash );

				$product = get_post( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) ) );
				update_post_meta( $post_id, 'WooCommerceEventsProductName', $product->post_title );

				/* Set ticket expiration, if enabled. */
				$event_id                = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) );
				$ticket_expire_type      = get_post_meta( $event_id, 'WooCommerceEventsTicketExpirationType', true );
				$ticket_expire_select    = get_post_meta( $event_id, 'WooCommerceEventsTicketsExpireSelectTimestamp', true );
				$ticket_expire_value     = get_post_meta( $event_id, 'WooCommerceEventsTicketsExpireValue', true );
				$ticket_expire_unit      = get_post_meta( $event_id, 'WooCommerceEventsTicketsExpireUnit', true );
				$ticket_expire_timestamp = '';

				if ( 'select' === $ticket_expire_type && ! empty( $ticket_expire_select ) ) {

					$ticket_expire_timestamp = $ticket_expire_select;

				} elseif ( 'time' === $ticket_expire_type && ! empty( $ticket_expire_value ) && ! empty( $ticket_expire_unit ) ) {

					$ticket_expire_timestamp = strtotime( '+' . $ticket_expire_value . ' ' . $ticket_expire_unit );

				}

				update_post_meta( $post_id, 'WooCommerceEventsTicketExpireTimestamp', $ticket_expire_timestamp );

				$this->zoom_api_helper->register_ticket_attendee( $post_id );

				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

					require_once ABSPATH . '/wp-admin/includes/plugin.php';

				}

				if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

					if ( isset( $_POST['WooCommerceEventsBookingSlotID'] ) && isset( $_POST['WooCommerceEventsBookingDateID'] ) ) {

						$fooevents_bookings = new FooEvents_Bookings();

						$fooevents_bookings->admin_add_ticket_bookings_capture_booking( $post_id, sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEvent'] ) ), sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingSlotID'] ) ), sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingDateID'] ) ) );

					}
				}

				$ticket_number = $this->increment_ticket_number( $_POST['WooCommerceEventsEvent'] );
				update_post_meta( $post_id, 'WooCommerceEventsTicketNumber', $ticket_number );

				$ticket_formated_number = $this->format_ticket_number( $_POST['WooCommerceEventsEvent'], $ticket_number );
				update_post_meta( $post_id, 'WooCommerceEventsTicketNumberFormatted', $ticket_formated_number );

				do_action( 'fooevents_create_ticket_admin', $post_id );

			}
		}

		remove_action( 'save_post', array( &$this, 'save_add_ticket_meta_boxes' ), 1, 2 );
	}

	/**
	 * Disable ticket post type auto save.
	 */
	public function disable_auto_save() {

		if ( 'event_magic_tickets' === get_post_type() ) {

			wp_dequeue_script( 'autosave' );

		}
	}

	/**
	 * Disable ticket drafts.
	 */
	public function disable_revisions() {

		remove_post_type_support( 'event_magic_tickets', 'revisions' );
	}

	/**
	 * Displays a tickets check-in log on the edit ticket page
	 */
	public function display_check_in_list() {

		$check_in_list_table = new Check_In_List_Table_Tickets();
		$check_in_list_table->prepare_items();

		$check_in_list_table->display();
	}

	/**
	 * Checks uploaded CSV if it has finished processing.
	 *
	 * @param array $file the uploaded CSV file.
	 * @return bool
	 */
	private function check_csv_finish_processing( $file ) {

		$saved_filename_md5  = get_option( 'fooevents_csv_filename_md5' );
		$saved_file_size     = get_option( 'fooevents_csv_size' );
		$saved_file_run      = get_option( 'fooevents_csv_run' );
		$saved_file_run_time = get_option( 'fooevents_csv_run_time' );
		$batch_size          = (int) get_option( 'globalWooCommerceEventsImportBatchSize' );

		if ( empty( $batch_size ) || 1 == $batch_size ) {

			$batch_size = 100;

		}

		$filename_md5 = md5( $file['fooevents-import-tickets-file']['name'] );
		$file_size    = $file['fooevents-import-tickets-file']['size'];
		$file_run     = $saved_file_run;

		$timestamp             = current_time( 'timestamp' );
		$twenty_four_hours_ago = $timestamp - 86400;

		// New file.
		if ( $saved_filename_md5 !== $filename_md5 && $saved_file_size !== $file_size || ( empty( $saved_file_run_time ) || $saved_file_run_time < $twenty_four_hours_ago ) ) {

			return false;

		}

		if ( empty( $file_run ) ) {

			$file_run = 1;

		}

		$temp_file              = $file['fooevents-import-tickets-file']['tmp_name'];
		$tickets_array_original = array_map( 'str_getcsv', file( $temp_file ) );
		$tickets_num_to_run     = $file_run * $batch_size;
		$tickets_num_to_start   = $tickets_num_to_run - $batch_size;

		if ( $file_run == 1 ) {

			$tickets_array = $this->ticket_array_slice( $tickets_array_original, 0, $batch_size );

		} else {

			$tickets_array = $this->ticket_array_slice( $tickets_array_original, $tickets_num_to_start, $batch_size );

		}

		$csv_rows = count( $tickets_array );

		if ( 1 === $csv_rows ) {

			return true;

		}

		return false;
	}

	/**
	 * Slices an array into require upload chunk based on run.
	 *
	 * @param array $array The original array.
	 * @param int   $start start of array to slice.
	 * @param int   $num number or elements to slice.
	 */
	private function ticket_array_slice( $original_array, $start = 0, $num = 100 ) {

		$processed_array = array();

		if ( $start > 0 ) {

			$processed_array[0] = $original_array[0];
			$num                = $num - 1;
		}

		$x = $start;
		$y = $start + $num;
		while ( $x <= $y ) {

			if ( isset( $original_array[ $x ] ) ) {

				$processed_array[ $x ] = $original_array[ $x ];

			}

			++$x;

		}

		return $processed_array;
	}

	/**
	 * Check if is edit page
	 *
	 * @param string $new_edit new edit.
	 * @return boolean
	 */
	private function is_edit_page( $new_edit = null ) {

		global $pagenow;

		if ( ! is_admin() ) {

			return false;
		}

		if ( 'edit' === $new_edit ) {

			return in_array( $pagenow, array( 'edit.php' ), true );

		} elseif ( 'new' === $new_edit ) {

			return in_array( $pagenow, array( 'post-new.php' ), true );

		} else {

			return in_array( $pagenow, array( 'post.php', 'post-new.php' ), true );

		}
	}

	/**
	 * Get usernames
	 *
	 * @return array
	 */
	private function get_usernames() {

		$users     = get_users( array( 'fields' => array( 'user_login' ) ) );
		$usernames = array();

		foreach ( $users as $user ) {

			$usernames[] = $user->user_login;

		}

		return $usernames;
	}

	/**
	 * Get user email addresses
	 *
	 * @return array
	 */
	private function get_user_emails() {

		$users     = get_users( array( 'fields' => array( 'user_email' ) ) );
		$usernames = array();

		foreach ( $users as $user ) {

			$usernames[] = $user->user_email;

		}

		return $usernames;
	}

	/**
	 * Get event variations on add ticket page
	 */
	public function get_event_variations() {

		global $woocommerce;

		if ( ! empty( $_POST['event_id'] ) ) {

			$event_id = sanitize_text_field( wp_unslash( $_POST['event_id'] ) );

			$variations = array();

			$handle      = new WC_Product_Variable( $event_id );
			$variations1 = $handle->get_children();
			foreach ( $variations1 as $value ) {

				$single_variation     = new WC_Product_Variation( $value );
				$variations[ $value ] = implode( ' / ', $single_variation->get_variation_attributes() ) . '-' . get_woocommerce_currency_symbol() . $single_variation->get_price();

			}

			if ( empty( $variations ) ) {

				echo wp_json_encode(
					array(
						'status'     => false,
						'variations' => array(),
					)
				);

			} else {

				echo wp_json_encode(
					array(
						'status'     => true,
						'variations' => $variations,
					)
				);

			}
		}

		exit();
	}

	/**
	 * Get event details on add ticket page
	 */
	public function get_event_details() {

		if ( ! empty( $_POST['event_id'] ) ) {

			$event_id = sanitize_text_field( wp_unslash( $_POST['event_id'] ) );
			$post     = get_post( $event_id );

			$event = array();

			$event['WooCommerceEventsName']           = $post->post_title;
			$event['WooCommerceEventsURL']            = get_permalink( $event_id );
			$event['WooCommerceEventsProductID']      = $event_id;
			$event['WooCommerceEventsDate']           = get_post_meta( $event_id, 'WooCommerceEventsDate', true );
			$event['WooCommerceEventsStartTime']      = get_post_meta( $event_id, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event_id, 'WooCommerceEventsMinutes', true );
			$event['WooCommerceEventsPeriod']         = get_post_meta( $event_id, 'WooCommerceEventsPeriod', true );
			$event['WooCommerceEventsEndTime']        = get_post_meta( $event_id, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event_id, 'WooCommerceEventsMinutesEnd', true );
			$event['WooCommerceEventsEndPeriod']      = get_post_meta( $event_id, 'WooCommerceEventsEndPeriod', true );
			$event['WooCommerceEventsLocation']       = get_post_meta( $event_id, 'WooCommerceEventsLocation', true );
			$event['WooCommerceEventsGPS']            = get_post_meta( $event_id, 'WooCommerceEventsGPS', true );
			$event['WooCommerceEventsSupportContact'] = get_post_meta( $event_id, 'WooCommerceEventsSupportContact', true );
			$event['WooCommerceEventsEmail']          = get_post_meta( $event_id, 'WooCommerceEventsEmail', true );
			$event['WooCommerceEventsType']           = get_post_meta( $event_id, 'WooCommerceEventsType', true );

			if ( 'bookings' !== $event['WooCommerceEventsType'] ) {

				$event_details_options = array(
					'WooCommerceEventsProductID' => $event_id,
				);

				$event['WooCommerceEventsZoomText'] = $this->zoom_api_helper->get_ticket_text( $event_details_options, 'admin' );

			}

			ob_start();
			require_once $this->config->template_path . 'tickets-add-ticket-event-details.php';
			$event_details = ob_get_clean();

			echo $event_details;

		}
		exit();
	}

	/**
	 * Processes resend ticket
	 *
	 * @param int $post_id post ID.
	 */
	public function resend_ticket( $post_id ) {

		if ( isset( $_POST['postID'] ) ) {

			$post_id = sanitize_text_field( wp_unslash( $_POST['postID'] ) );

		}

		$ticket = $this->get_ticket_data( $post_id );

		$order = wc_get_order( $ticket['WooCommerceEventsOrderID'] );

		$billing_first_name = '';
		$billing_last_name  = '';
		$billing_email      = '';

		if ( false !== $order ) {

			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$billing_email      = $order->get_billing_email();

		}

		$merge_fields_global = array(
			'{OrderNumber}'       => '[#' . $ticket['WooCommerceEventsOrderID'] . ']',
			'{OrderNumberOnly}'   => $ticket['WooCommerceEventsOrderID'],
			'{EventName}'         => get_the_title( $ticket['WooCommerceEventsProductID'] ),
			'{EventVenue}'        => $ticket['WooCommerceEventsLocation'],
			'{EventDate}'         => $ticket['WooCommerceEventsDate'],
			'{EventHour}'         => $ticket['WooCommerceEventsHour'],
			'{EventMinute}'       => $ticket['WooCommerceEventsMinutes'],
			'{EventPeriod}'       => $ticket['WooCommerceEventsPeriod'],
			'{EventEndDate}'      => $ticket['WooCommerceEventsEndDate'],
			'{EventHourEnd}'      => $ticket['WooCommerceEventsHourEnd'],
			'{EventMinuteEnd}'    => $ticket['WooCommerceEventsMinutesEnd'],
			'{EventPeriodEnd}'    => $ticket['WooCommerceEventsEndPeriod'],
			'{CustomerFirstName}' => $billing_first_name,
			'{CustomerLastName}'  => $billing_last_name,
			'{CustomerEmail}'     => $billing_email,
			'{AttendeeFName}'     => $ticket['WooCommerceEventsAttendeeName'],
			'{AttendeeLName}'     => $ticket['WooCommerceEventsAttendeeLastName'],
			'{AttendeeEmail}'     => $ticket['WooCommerceEventsAttendeeEmail'],
			'{TicketID}'          => $ticket['WooCommerceEventsTicketID'],
			'{BookingsSlot}'      => $ticket['WooCommerceEventsBookingSlot'],
			'{BookingsDate}'      => $ticket['WooCommerceEventsBookingDate'],
			'{SeatingRow}'        => $ticket['fooevents_seating_options_array']['row_name'],
			'{SeatingSeat}'       => $ticket['fooevents_seating_options_array']['seat_number'],
		);

		$product_id               = get_post_meta( $post_id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_event = get_post_meta( $product_id, 'WooCommerceEventsEvent', true );

		$customer_details = array();

		$order_id         = get_post_meta( $post_id, 'WooCommerceEventsOrderID', true );
		$customer_id      = get_post_meta( $post_id, 'WooCommerceEventsCustomerID', true );
		$event_id         = get_post_meta( $post_id, 'WooCommerceEventsProductID', true );
		$attendee_email   = get_post_meta( $post_id, 'WooCommerceEventsAttendeeEmail', true );
		$send_to_attendee = get_post_meta( $event_id, 'WooCommerceEventsEmailAttendee', true );

		$woocommerce_events_email_subject_single = get_post_meta( $product_id, 'WooCommerceEventsEmailSubjectSingle', true );
		if ( empty( $woocommerce_events_email_subject_single ) ) {

			$woocommerce_events_email_subject_single = __( '{OrderNumber} Ticket', 'woocommerce-events' );

		}

		$subject     = strtr( $woocommerce_events_email_subject_single, $merge_fields_global );
		$ticket_body = '';

		if ( false !== $order ) {

			if ( ! empty( $order->get_billing_first_name() ) ) {

				$customer_details['customerFirstName'] = $order->get_billing_first_name();

			} else {

				$customer_details['customerFirstName'] = get_post_meta( $post_id, 'WooCommerceEventsPurchaserFirstName', true );

			}

			if ( ! empty( $order->get_billing_last_name() ) ) {

				$customer_details['customerLastName'] = $order->get_billing_last_name();

			} else {

				$customer_details['customerLastName'] = get_post_meta( $post_id, 'WooCommerceEventsPurchaserLastName', true );

			}

			if ( ! empty( $order->get_billing_email() ) ) {

				$customer_details['customerEmail'] = $order->get_billing_email();

			} else {

				$customer_details['customerEmail'] = get_post_meta( $post_id, 'WooCommerceEventsPurchaserEmail', true );

			}
		} else {

			$customer_details['customerFirstName'] = '';
			$customer_details['customerLastName']  = '';
			$customer_details['customerEmail']     = '';

		}

		$woocommerce_events_ticket_theme = get_post_meta( $product_id, 'WooCommerceEventsTicketTheme', true );
		if ( empty( $woocommerce_events_ticket_theme ) ) {

			$woocommerce_events_ticket_theme = $this->config->email_template_path;

		}

		$header                 = $this->mail_helper->parse_email_template( $woocommerce_events_ticket_theme . '/header.php', $ticket, array() );
		$footer                 = $this->mail_helper->parse_email_template( $woocommerce_events_ticket_theme . '/footer.php', $ticket, array() );
		$ticket['ticketNumber'] = 1;

		$body = $this->mail_helper->parse_ticket_template( $woocommerce_events_ticket_theme . '/ticket.php', $ticket );
		$body = strtr( $body, $merge_fields_global );

		$ticket_body .= $body;

		$to = '';
		if ( isset( $_POST['WooCommerceEventsResendTicketEmail'] ) ) {

			$to = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsResendTicketEmail'] ) );

		} elseif ( ! empty( $woocommerce_events_attendee_email ) && 1 !== $woocommerce_events_attendee_email ) {

			$to = $woocommerce_events_attendee_email;

		} else {

			$attendee_email = get_post_meta( $post_id, 'WooCommerceEventsAttendeeEmail', true );

			if ( 'on' === $send_to_attendee && ! empty( $attendee_email ) ) {

				$to = $attendee_email;

			} else {

				$to = $customer_details['customerEmail'];

			}
		}

		if ( empty( $to ) && ! empty( $attendee_email ) ) {

			$to = $attendee_email;

		}

		$attachments = array();
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

			$global_fooevents_pdf_tickets_enable             = get_option( 'globalFooEventsPDFTicketsEnable' );
			$global_fooevents_pdf_tickets_attach_html_ticket = get_option( 'globalFooEventsPDFTicketsAttachHTMLTicket' );

			if ( 'yes' === $global_fooevents_pdf_tickets_enable ) {

				$fooevents_pdf_tickets            = new FooEvents_PDF_Tickets();
				$attachments[]                    = $fooevents_pdf_tickets->generate_ticket( $product_id, array( $ticket ), $this->config->barcode_path, $this->config->path );
				$fooevents_pdf_tickets_email_text = '<div class="fooevents-pdf-email-text">' . nl2br( get_post_meta( $product_id, 'FooEventsPDFTicketsEmailText', true ) ) . '</div>';

				if ( 'yes' === $global_fooevents_pdf_tickets_attach_html_ticket ) {

					$header      = $header . $fooevents_pdf_tickets_email_text;
					$ticket_body = $body;

				} else {

					$header = $fooevents_pdf_tickets->parse_email_template( 'email-header.php' );
					$footer = $fooevents_pdf_tickets->parse_email_template( 'email-footer.php' );

					$ticket_body = $fooevents_pdf_tickets_email_text . $footer;

				}
			}
		}

		// attach ics.
		$woocommerce_events_ticket_attach_ics = get_post_meta( $product_id, 'WooCommerceEventsTicketAttachICS', true );

		if ( ! empty( $woocommerce_events_ticket_attach_ics ) && 'on' === $woocommerce_events_ticket_attach_ics && file_exists( $this->config->ics_path . $ticket['WooCommerceEventsTicketID'] . '.ics' ) ) {

			$attachments[] = $this->config->ics_path . '' . $ticket['WooCommerceEventsTicketID'] . '.ics';

		}

		$mail_status = $this->mail_helper->send_ticket( $to, $subject, $header . $ticket_body . $footer, $attachments, $product_id );

		if ( isset( $_POST['postID'] ) ) {

			echo wp_json_encode( array( 'message' => 'Mail has been sent.' ) );
			exit();

		}
	}

	/**
	 * Retrieves ticket data from database.
	 *
	 * @param int    $ticket_id ticket ID.
	 * @param string $display display.
	 * @return array
	 */
	public function get_ticket_data( $ticket_id, $display = '' ) {

		$ticket = array();

		$woocommerce_events_product_id                = get_post_meta( $ticket_id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_order_id                  = get_post_meta( $ticket_id, 'WooCommerceEventsOrderID', true );
		$woocommerce_events_ticket_type               = get_post_meta( $ticket_id, 'WooCommerceEventsTicketType', true );
		$woocommerce_events_ticket_id                 = get_post_meta( $ticket_id, 'WooCommerceEventsTicketID', true );
		$woocommerce_events_ticket_hash               = get_post_meta( $ticket_id, 'WooCommerceEventsTicketHash', true );
		$woocommerce_events_status                    = get_post_meta( $ticket_id, 'WooCommerceEventsStatus', true );
		$woocommerce_events_ticket_expire_timestamp   = get_post_meta( $ticket_id, 'WooCommerceEventsTicketExpireTimestamp', true );
		$woocommerce_events_ticket_number_formatted   = get_post_meta( $ticket_id, 'WooCommerceEventsTicketNumberFormatted', true );
		$woocommerce_events_booking_date_timestamp    = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateTimestamp', true );
		$woocommerce_events_booking_date_mysql_format = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateMySQLFormat', true );
		$woocommerce_events_ticket_number             = get_post_meta( $ticket_id, 'WooCommerceEventsTicketNumber', true );

		$wp_date_format         = get_option( 'date_format' );
		$date_format            = $wp_date_format . ' H:i';
		$ticket_expiration_date = '';

		if ( ! empty( $woocommerce_events_ticket_expire_timestamp ) ) {

			if ( ! is_int( $woocommerce_events_ticket_expire_timestamp ) ) {

				$woocommerce_events_ticket_expire_timestamp = strtotime( $woocommerce_events_ticket_expire_timestamp );

			}

			$ticket_expiration_date = date( $date_format, $woocommerce_events_ticket_expire_timestamp );

		}

		$ticket['WooCommerceEventsVariations']  = get_post_meta( $ticket_id, 'WooCommerceEventsVariations', true );
		$ticket['WooCommerceEventsPrice']       = get_post_meta( $ticket_id, 'WooCommerceEventsPrice', true );
		$ticket['WooCommerceEventsPriceSymbol'] = get_post_meta( $ticket_id, 'WooCommerceEventsPriceSymbol', true );

		$ticket['type']                         = 'HTML';
		$ticket['WooCommerceEventsVariationID'] = get_post_meta( $ticket_id, 'WooCommerceEventsVariationID', true );
		$ticket['WooCommerceEventsCreateType']  = get_post_meta( $ticket_id, 'WooCommerceEventsCreateType', true );

		$one = get_post_meta( $ticket_id, 'WooCommerceEventsVariations', true );

		if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) {

			$ticket_vars = array();
			foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_value ) {

				$variation_name_output = str_replace( 'attribute_', '', $variation_name );
				$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
				$variation_name_output = str_replace( '_', ' ', $variation_name_output );
				$variation_name_output = str_replace( '-', ' ', $variation_name_output );
				$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
				$variation_name_output = ucwords( $variation_name_output );
				$variation_name_output = urldecode( $variation_name_output );

				$variation_value_output = str_replace( '_', ' ', $variation_value );
				$variation_value_output = str_replace( '-', ' ', $variation_value_output );
				$variation_value_output = ucwords( $variation_value_output );

				$ticket_vars[ $variation_name_output ] = $variation_value_output;

			}

			$ticket['WooCommerceEventsVariations'] = $ticket_vars;

			// Using the variation ID is unreliable for 'Any' variations, rather use old method.
			$ticket['WooCommerceEventsVariationsFromID'] = $ticket_vars;

			// Alternative variations for users that require different encoding.
			if ( ! empty( $ticket['WooCommerceEventsVariationID'] ) ) {

				$variation_obj = new WC_Product_variation( $ticket['WooCommerceEventsVariationID'] );
				$variations    = $variation_obj->get_attributes();

				if ( ! empty( $variations ) ) {
					$ticket_variations = array();

					foreach ( $variations as $key => $value ) {
						// Global.
						if ( strpos( $key, 'pa_' ) === 0 ) {

							$value = ucfirst( $value );
							$value = str_replace( '-', ' ', $value );
							$ticket_variations[ trim( wc_attribute_label( $key, $variation_obj ) ) ] = trim( $value );

						} else {

							$ticket_variations[ trim( wc_attribute_label( $key, $variation_obj ) ) ] = trim( $value );

						}
					}

					$ticket['WooCommerceEventsVariationsFromIDAlt'] = $ticket_variations;

				}
			}
		}

		$order = wc_get_order( $woocommerce_events_order_id );

		if ( false !== $order ) {

			$customer_details['customerFirstName'] = $order->get_billing_first_name();
			$customer_details['customerLastName']  = $order->get_billing_last_name();
			$customer_details['customerEmail']     = $order->get_billing_email();
			$customer_details['customerTelephone'] = $order->get_billing_phone();
			$customer_details['customerID']        = $order->get_customer_id();

			if ( empty( $customer_details['customerFirstName'] ) ) {

				$user = get_user_by( 'id', $order->get_customer_id() );

				if ( false !== $user ) {
					$customer_details['customerFirstName'] = $user->display_name;
				}
			}
		} else {

			$customer_details['customerFirstName'] = '';
			$customer_details['customerLastName']  = '';
			$customer_details['customerEmail']     = '';

		}

		$ticket['fooevents_custom_attendee_fields_options'] = '';
		$ticket['fooevents_seating_options']                = '';

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			$fooevents_custom_attendee_fields                         = new Fooevents_Custom_Attendee_Fields();
			$ticket['fooevents_custom_attendee_fields_options']       = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_output_legacy( $ticket_id, $woocommerce_events_product_id );
			$ticket['fooevents_custom_attendee_fields_options_array'] = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_output( $ticket_id, $woocommerce_events_product_id );

		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating                         = new Fooevents_Seating();
			$ticket['fooevents_seating_options']       = $fooevents_seating->display_tickets_meta_seat_options_output_legacy( $ticket_id );
			$ticket['fooevents_seating_options_array'] = $fooevents_seating->display_tickets_meta_seat_options_output( $ticket_id );
			$ticket['WooCommerceEventsSeatingFields']  = get_post_meta( $ticket_id, 'WooCommerceEventsSeatingFields', true );

			$ticket['WooCommerceEventsSeatingRowOverride'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingRowOverride', true );

			if ( '' === $ticket['WooCommerceEventsSeatingRowOverride'] ) {
				$ticket['WooCommerceEventsSeatingRowOverride'] = __( 'Row', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingRowOverridePlural'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingRowOverridePlural', true );

			if ( '' === $ticket['WooCommerceEventsSeatingRowOverridePlural'] ) {
				$ticket['WooCommerceEventsSeatingRowOverridePlural'] = __( 'Rows', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingSeatOverride'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingSeatOverride', true );

			if ( '' === $ticket['WooCommerceEventsSeatingSeatOverride'] ) {
				$ticket['WooCommerceEventsSeatingSeatOverride'] = __( 'Seat', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingSeatOverridePlural'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingSeatOverridePlural', true );

			if ( '' === $ticket['WooCommerceEventsSeatingSeatOverridePlural'] ) {
				$ticket['WooCommerceEventsSeatingSeatOverridePlural'] = __( 'Seats', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingSeatingChartOverride'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingSeatingChartOverride', true );

			if ( '' === $ticket['WooCommerceEventsSeatingSeatingChartOverride'] ) {
				$ticket['WooCommerceEventsSeatingSeatingChartOverride'] = __( 'Seating Chart', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingSeatingChartOverridePlural'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingSeatingChartOverridePlural', true );

			if ( '' === $ticket['WooCommerceEventsSeatingSeatingChartOverridePlural'] ) {
				$ticket['WooCommerceEventsSeatingSeatingChartOverridePlural'] = __( 'Seating Charts', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingFrontOverride'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingFrontOverride', true );

			if ( '' === $ticket['WooCommerceEventsSeatingFrontOverride'] ) {
				$ticket['WooCommerceEventsSeatingFrontOverride'] = __( 'FRONT', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingFrontOverridePlural'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSeatingFrontOverridePlural', true );

			if ( '' === $ticket['WooCommerceEventsSeatingFrontOverridePlural'] ) {
				$ticket['WooCommerceEventsSeatingFrontOverridePlural'] = __( 'FRONTS', 'woocommerce-events' );
			}
		}

		$woocommerce_events_event                    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEvent', true );
		$woocommerce_events_capture_attendee_details = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsCaptureAttendeeDetails', true );
		$woocommerce_events_send_email_tickets       = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSendEmailTickets', true );
		$pos_enable_ticket_emails                    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsPOSEnableTicketEmails', true );
		$woocommerce_events_email_subject_single     = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEmailSubjectSingle', true );

		$ticket['WooCommerceEventsEvent']                  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEvent', true );
		$ticket['WooCommerceEventsDate']                   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDate', true );
		$ticket['WooCommerceEventsDateMySQLFormat']        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDateMySQLFormat', true );
		$ticket['WooCommerceEventsDateTimestamp']          = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDateTimestamp', true );
		$ticket['WooCommerceEventsDateTimeTimestamp']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDateTimeTimestamp', true );
		$ticket['WooCommerceEventsEndDateTimestamp']       = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndDateTimestamp', true );
		$ticket['WooCommerceEventsEndDateTimeTimestamp']   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndDateTimeTimestamp', true );
		$ticket['WooCommerceEventsEndDate']                = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndDate', true );
		$ticket['WooCommerceEventsEndDateMySQLFormat']     = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndDateMySQLFormat', true );
		$ticket['WooCommerceEventsBookingDateTimestamp']   = $woocommerce_events_booking_date_timestamp;
		$ticket['WooCommerceEventsBookingDateMySQLFormat'] = $woocommerce_events_booking_date_mysql_format;

		$ticket['WooCommerceEventsSelectDate']           = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDate', true );
		$ticket['WooCommerceEventsSelectDateHour']       = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateHour', true );
		$ticket['WooCommerceEventsSelectDateMinutes']    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateMinutes', true );
		$ticket['WooCommerceEventsSelectDatePeriod']     = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDatePeriod', true );
		$ticket['WooCommerceEventsSelectDateHourEnd']    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateHourEnd', true );
		$ticket['WooCommerceEventsSelectDateMinutesEnd'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateMinutesEnd', true );
		$ticket['WooCommerceEventsSelectDatePeriodEnd']  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDatePeriodEnd', true );
		$ticket['WooCommerceEventsSelectGlobalTime']     = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectGlobalTime', true );

		$ticket['WooCommerceEventsMultiDayType']           = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsMultiDayType', true );
		$ticket['WooCommerceEventsHour']                   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsHour', true );
		$ticket['WooCommerceEventsMinutes']                = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsMinutes', true );
		$ticket['WooCommerceEventsPeriod']                 = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsPeriod', true );
		$ticket['WooCommerceEventsHourEnd']                = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsHourEnd', true );
		$ticket['WooCommerceEventsMinutesEnd']             = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsMinutesEnd', true );
		$ticket['WooCommerceEventsEndPeriod']              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndPeriod', true );
		$ticket['WooCommerceEventsTimeZone']               = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTimeZone', true );
		$ticket['WooCommerceEventsLocation']               = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsLocation', true );
		$ticket['WooCommerceEventsTicketLogo']             = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketLogo', true );
		$ticket['WooCommerceEventsTicketHeaderImage']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketHeaderImage', true );
		$ticket['WooCommerceEventsSupportContact']         = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSupportContact', true );
		$ticket['WooCommerceEventsEmail']                  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEmail', true );
		$ticket['WooCommerceEventsTicketBackgroundColor']  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketBackgroundColor', true );
		$ticket['WooCommerceEventsTicketButtonColor']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketButtonColor', true );
		$ticket['WooCommerceEventsTicketTextColor']        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketTextColor', true );
		$ticket['WooCommerceEventsTicketPurchaserDetails'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketPurchaserDetails', true );
		$ticket['WooCommerceEventsTicketAddCalendar']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketAddCalendar', true );
		$ticket['WooCommerceEventsTicketDisplayDateTime']  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayDateTime', true );
		$ticket['WooCommerceEventsTicketDisplayBarcode']   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayBarcode', true );
		$ticket['WooCommerceEventsTicketText']             = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketText', true );
		$ticket['WooCommerceEventsType']                   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
		$ticket['WooCommerceEventsZoomMultiOption']        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomMultiOption', true );
		$ticket['WooCommerceEventsTicketDisplayMultiDay']  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayMultiDay', true );
		$ticket['dayTerm']                                 = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );
		$ticket['WooCommerceEventsTicketIdentifierOutput'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketIdentifierOutput', true );

		if ( empty( $ticket['WooCommerceEventsTicketIdentifierOutput'] ) ) {

			$ticket['WooCommerceEventsTicketIdentifierOutput'] = 'ticketid';

		}

		if ( empty( $ticket['dayTerm'] ) || 1 == $ticket['dayTerm'] ) {

			$ticket['dayTerm'] = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $ticket['dayTerm'] ) || 1 == $ticket['dayTerm'] ) {

			$ticket['dayTerm'] = __( 'Day', 'woocommerce-events' );

		}

		if ( empty( $ticket['dayTerm'] ) || 1 == $ticket['dayTerm'] ) {

			$ticket['dayTerm'] = __( 'Day', 'woocommerce-events' );

		}

		if ( 'admin' !== $display || ( 'bookings' === $ticket['WooCommerceEventsType'] && 'bookings' === $ticket['WooCommerceEventsZoomMultiOption'] ) ) {

			$woocommerce_events_booking_slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );
			$woocommerce_events_booking_date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );

			$ticket_text_options = array(
				'WooCommerceEventsProductID' => $woocommerce_events_product_id,
				'slot_id'                    => $woocommerce_events_booking_slot_id,
				'date_id'                    => $woocommerce_events_booking_date_id,
				'registrant_email'           => get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true ),
			);

			$ticket['WooCommerceEventsZoomText'] = $this->zoom_api_helper->get_ticket_text( $ticket_text_options, $display );

		}

		$ticket['WooCommerceEventsDirections']                   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDirections', true );
		$ticket['WooCommerceEventsTicketDisplayPrice']           = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayPrice', true );
		$ticket['WooCommerceEventsTicketDisplayZoom']            = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayZoom', true );
		$ticket['WooCommerceEventsTicketDisplayAddToWallet']     = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayAddToWallet', true );
		$ticket['WooCommerceEventsTicketDisplayBookings']        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketDisplayBookings', true );
		$ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsIncludeCustomAttendeeDetails', true );

		$ticket['WooCommerceEventsTicketLogoPath']        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTicketLogoPath', true );
		$ticket['WooCommerceEventsTicketType']            = $woocommerce_events_ticket_type;
		$ticket['WooCommerceEventsProductID']             = $woocommerce_events_product_id;
		$ticket['WooCommerceEventsTicketID']              = $woocommerce_events_ticket_id;
		$ticket['WooCommerceEventsTicketHash']            = $woocommerce_events_ticket_hash;
		$ticket['WooCommerceEventsOrderID']               = $woocommerce_events_order_id;
		$ticket['WooCommerceEventsTicketNumberFormatted'] = $woocommerce_events_ticket_number_formatted;
		$ticket['WooCommerceEventsTicketNumber']          = $woocommerce_events_ticket_number;
		$ticket['TicketNumberTicketOutput']               = '';

		if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] && ! empty( $ticket['WooCommerceEventsTicketNumberFormatted'] ) ) {

			$ticket['TicketNumberTicketOutput'] = $ticket['WooCommerceEventsTicketNumberFormatted'];

		} else {

			$ticket['TicketNumberTicketOutput'] = $ticket['WooCommerceEventsTicketID'];

		}

		if ( ! empty( $order ) ) {

			$ticket['WooCommerceEventsOrderStatus'] = ucfirst( $order->get_status() );
			$ticket['WooCommerceEventsOrderTotal']  = wc_price( $order->get_total() );
			$ticket['customerID']                   = $order->get_customer_id();
			$ticket['customerPhone']                = $order->get_billing_phone();

		} else {

			$ticket['WooCommerceEventsOrderStatus'] = '';
			$ticket['WooCommerceEventsOrderTotal']  = '';
			$ticket['customerID']                   = '';
			$ticket['customerPhone']                = '';

		}

		$ticket['WooCommerceEventsSendEmailTickets']      = $woocommerce_events_send_email_tickets;
		$ticket['WooCommerceEventsPOSEnableTicketEmails'] = $pos_enable_ticket_emails;
		$ticket['WooCommerceEventsTicketExpireTimestamp'] = $woocommerce_events_ticket_expire_timestamp;
		$ticket['WooCommerceEventsTicketExpireFormatted'] = $ticket_expiration_date;
		$ticket['ID']                                     = $ticket_id;

		$event = get_post( $woocommerce_events_product_id );

		$ticket['WooCommerceEventsName'] = $event->post_title;
		$ticket['WooCommerceEventsURL']  = get_permalink( $event->ID );

		$ticket['WooCommerceEventsDate']      = get_post_meta( $event->ID, 'WooCommerceEventsDate', true );
		$ticket['WooCommerceEventsStartTime'] = get_post_meta( $event->ID, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
		$ticket['WooCommerceEventsEndTime']   = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );
		$ticket['WooCommerceEventsPeriod']    = get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
		$ticket['WooCommerceEventsEndPeriod'] = get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );

		$ticket['WooCommerceEventsLocation']       = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
		$ticket['WooCommerceEventsGPS']            = get_post_meta( $event->ID, 'WooCommerceEventsGPS', true );
		$ticket['WooCommerceEventsSupportContact'] = get_post_meta( $event->ID, 'WooCommerceEventsSupportContact', true );
		$ticket['WooCommerceEventsEmail']          = get_post_meta( $event->ID, 'WooCommerceEventsEmail', true );

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$fooevents_bookings = new FooEvents_Bookings();
			$bookings_data      = $fooevents_bookings->get_ticket_slot_and_date( $ticket_id, $event->ID );

			$bookings_date_term           = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term           = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsSlotOverride', true );
			$bookings_bookingdetails_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsBookingDetailsOverride', true );

			$ticket['WooCommerceEventsBookingSlot'] = $bookings_data['slot'];
			$ticket['WooCommerceEventsBookingDate'] = $bookings_data['date'];

			if ( empty( $bookings_date_term ) ) {

				$ticket['WooCommerceEventsBookingsDateTerm'] = __( 'Date', 'woocommerce-events' );

			} else {

				$ticket['WooCommerceEventsBookingsDateTerm'] = $bookings_date_term;

			}

			if ( empty( $bookings_slot_term ) ) {

				$ticket['WooCommerceEventsBookingsSlotTerm'] = __( 'Slot', 'woocommerce-events' );

			} else {

				$ticket['WooCommerceEventsBookingsSlotTerm'] = $bookings_slot_term;

			}
		}

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			if ( 'select' === $ticket['WooCommerceEventsType'] ) {

				if ( ! isset( $ticket['WooCommerceEventsSelectDate'] ) || ! is_array( $ticket['WooCommerceEventsSelectDate'] ) ) {

					$ticket['WooCommerceEventsSelectDate'] = array( $ticket['WooCommerceEventsSelectDate'] );

				}

				$ticket['WooCommerceEventsDate']    = $ticket['WooCommerceEventsSelectDate'][0];
				$ticket['WooCommerceEventsEndDate'] = $ticket['WooCommerceEventsSelectDate'][ count( $ticket['WooCommerceEventsSelectDate'] ) - 1 ];

			}
		}

		$barcode_file_name = '';

		if ( ! empty( $woocommerce_events_ticket_hash ) ) {

			$barcode_file_name = $woocommerce_events_ticket_hash . '-' . $woocommerce_events_ticket_id;

		} else {

			$barcode_file_name = $woocommerce_events_ticket_id;

		}

		$ticket['barcodeFileName'] = $barcode_file_name;
		$ticket['barcodeURL']      = $this->config->barcode_url;

		$ticket_details = get_post( $woocommerce_events_product_id );

		$ticket['WooCommerceEventsTicketText'] = apply_filters( 'meta_content', $ticket['WooCommerceEventsTicketText'] );

		if ( ! empty( $ticket['WooCommerceEventsTicketLogo'] ) ) {

				$logo_id = attachment_url_to_postid( $ticket['WooCommerceEventsTicketLogo'] );

			if ( $logo_id ) {

				$ticket['WooCommerceEventsTicketLogoID']   = $logo_id;
				$ticket['WooCommerceEventsTicketLogoPath'] = get_attached_file( $ticket['WooCommerceEventsTicketLogoID'] );

			} else {

				$ticket['WooCommerceEventsTicketLogoPath'] = $ticket['WooCommerceEventsTicketLogo'];

			}
		}

		if ( ! empty( $ticket['WooCommerceEventsTicketHeaderImage'] ) ) {

			$header_image_id = attachment_url_to_postid( $ticket['WooCommerceEventsTicketHeaderImage'] );

			if ( $header_image_id ) {

				$ticket['WooCommerceEventsTicketHeaderImageID']   = $header_image_id;
				$ticket['WooCommerceEventsTicketHeaderImagePath'] = get_attached_file( $ticket['WooCommerceEventsTicketHeaderImageID'] );

			} else {

				$ticket['WooCommerceEventsTicketHeaderImagePath'] = $ticket['WooCommerceEventsTicketHeaderImage'];

			}
		}

		$global_woocommerce_events_ticket_background_color = get_option( 'globalWooCommerceEventsTicketBackgroundColor', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_background_color ) {

			$global_woocommerce_events_ticket_background_color = '';

		}

		$global_woocommerce_events_ticket_button_color = get_option( 'globalWooCommerceEventsTicketButtonColor', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_button_color ) {

			$global_woocommerce_events_ticket_button_color = '';

		}

		$global_woocommerce_events_ticket_text_color = get_option( 'globalWooCommerceEventsTicketTextColor', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_text_color ) {

			$global_woocommerce_events_ticket_text_color = '';

		}

		$global_woocommerce_events_ticket_logo = get_option( 'globalWooCommerceEventsTicketLogo', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_logo ) {

			$global_woocommerce_events_ticket_logo = '';

		}

		if ( empty( $ticket['WooCommerceEventsTicketBackgroundColor'] ) ) {

			$ticket['WooCommerceEventsTicketBackgroundColor'] = $global_woocommerce_events_ticket_background_color;

		}

		if ( empty( $ticket['WooCommerceEventsTicketButtonColor'] ) ) {

			$ticket['WooCommerceEventsTicketButtonColor'] = $global_woocommerce_events_ticket_button_color;

		}

		if ( empty( $ticket['WooCommerceEventsTicketTextColor'] ) ) {

			$ticket['WooCommerceEventsTicketTextColor'] = $global_woocommerce_events_ticket_text_color;

		}

		$global_woocommerce_events_display_poweredby = get_option( 'globalWooCommerceEventsDisplayPoweredby', true );

		$ticket['WooCommerceEventsDisplayPoweredby'] = $global_woocommerce_events_display_poweredby;

		if ( empty( $ticket['name'] ) ) {

			$ticket['name'] = $ticket_details->post_title;

		}

		$timestamp            = time();
		$key                  = md5( $woocommerce_events_ticket_id . $timestamp . $this->config->salt );
		$ticket['cancelLink'] = get_site_url() . '/wp-admin/admin-ajax.php?action=woocommerce_events_cancel&id=' . $woocommerce_events_ticket_id . '&t=' . $timestamp . '&k=' . $key;

		$ticket['WooCommerceEventsAttendeeName']        = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeName', true );
		$ticket['WooCommerceEventsAttendeeLastName']    = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeLastName', true );
		$ticket['WooCommerceEventsAttendeeTelephone']   = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeTelephone', true );
		$ticket['WooCommerceEventsAttendeeCompany']     = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeCompany', true );
		$ticket['WooCommerceEventsAttendeeDesignation'] = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeDesignation', true );
		$ticket['WooCommerceEventsAttendeeEmail']       = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true );
		$ticket['WooCommerceEventsZoomJoinURL']         = get_post_meta( $ticket_id, 'WooCommerceEventsZoomJoinURL', true );

		if ( empty( $ticket['WooCommerceEventsAttendeeName'] ) ) {

			$ticket['WooCommerceEventsAttendeeName'] = $customer_details['customerFirstName'];

		}

		if ( empty( $ticket['WooCommerceEventsAttendeeLastName'] ) ) {

			$ticket['WooCommerceEventsAttendeeLastName'] = $customer_details['customerLastName'];

		}

		if ( empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ) {

			$ticket['WooCommerceEventsAttendeeEmail'] = $customer_details['customerEmail'];

		}

		if ( empty( $ticket['WooCommerceEventsAttendeeName'] ) && empty( $ticket['WooCommerceEventsAttendeeLastName'] ) && empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) {

			$ticket['WooCommerceEventsAttendeeTelephone'] = $customer_details['customerTelephone'];

		}

		$ticket['customerFirstName'] = $customer_details['customerFirstName'];
		$ticket['customerLastName']  = $customer_details['customerLastName'];
		$ticket['customerEmail']     = $customer_details['customerEmail'];

		// generate barcodes.

		if ( ! file_exists( $this->config->barcode_path . $woocommerce_events_ticket_hash . '-' . $ticket['WooCommerceEventsTicketID'] . '.png' ) ) {

			$this->barcode_helper->generate_barcode( $ticket['WooCommerceEventsTicketID'], $woocommerce_events_ticket_hash );

		}

		if ( ! empty( $woocommerce_events_ticket_number_formatted ) ) {

			if ( ! file_exists( $this->config->barcode_path . $woocommerce_events_ticket_hash . '-' . $ticket['WooCommerceEventsTicketID'] . '-tn.png' ) ) {

				$this->barcode_helper->generate_barcode( $woocommerce_events_product_id . '-' . $woocommerce_events_ticket_number_formatted, $woocommerce_events_ticket_hash, $ticket['WooCommerceEventsTicketID'], 'tn' );

			}
		}

		$this->ics_helper->generate_ics( $woocommerce_events_product_id, $ticket_id, ! empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ? $ticket['WooCommerceEventsAttendeeEmail'] : '' );
		$this->ics_helper->save( $ticket['WooCommerceEventsTicketID'] );

		$ticket['FooEventsTicketFooterText'] = get_post_meta( $woocommerce_events_product_id, 'FooEventsTicketFooterText', true );

		if ( empty( $ticket['WooCommerceEventsTicketBackgroundColor'] ) ) {

			$ticket['WooCommerceEventsTicketBackgroundColor'] = '#f5f5f5';

		}

		if ( empty( $ticket['WooCommerceEventsTicketButtonColor'] ) ) {

			$ticket['WooCommerceEventsTicketButtonColor'] = '#16A75D';

		}

		if ( empty( $ticket['WooCommerceEventsTicketTextColor'] ) ) {

			$ticket['WooCommerceEventsTicketTextColor'] = '#FFFFFF';

		}

		if ( '' !== $ticket['WooCommerceEventsTimeZone'] ) {

			$timezone_date  = '';
			$wp_date_format = get_option( 'date_format' );
			$date_from      = sanitize_text_field( wp_unslash( $ticket['WooCommerceEventsDate'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'd/m/Y' === $wp_date_format || 'j F Y' === $wp_date_format ) {
				$date_from = str_replace( '/', '-', $date_from );
			}

			try {

				$timezone_date = new DateTime( $this->convert_month_to_english( $date_from ) );

			} catch ( Exception $e ) {

				$timezone_date = '';

			}

			try {

				$tz = new DateTimeZone( $ticket['WooCommerceEventsTimeZone'] );

			} catch ( Exception $e ) {

				$server_timezone = date_default_timezone_get();
				$tz              = new DateTimeZone( $server_timezone );

			}

			if ( ! empty( $timezone_date ) ) {

				$timezone_date->setTimeZone( $tz );
				$ticket['WooCommerceEventsTimeZone'] = $timezone_date->format( 'T' );
				if ( (int) $ticket['WooCommerceEventsTimeZone'] > 0 ) {
					$ticket['WooCommerceEventsTimeZone'] = 'UTC' . $ticket['WooCommerceEventsTimeZone'];
				}
			}
		}

		return $ticket;
	}

	/**
	 * Returns image url attachment. TODO: Remove if not needed.
	 *
	 * @param string $image_url image URL.
	 * @global object $wpdb
	 * @return boolean
	 */
	public function get_logo_id( $image_url ) {
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );

		if ( ! empty( $attachment[0] ) ) {

			return $attachment[0];

		} else {

			return false;

		}
	}

	/**
	 * Adds event filter selection to the ticket listing
	 */
	public function filter_ticket_options() {

		global $wpdb;
		global $post_type;

		if ( 'event_magic_tickets' === $post_type ) {

			$prefix = $wpdb->prefix;

			$event_id = '';

			if ( isset( $_GET['event_id'] ) && '' !== $_GET['event_id'] ) {

				$event_id = sanitize_text_field( wp_unslash( $_GET['event_id'] ) );

			}

			$events = $wpdb->get_results(
				'   SELECT *
                    FROM ' . $prefix . 'posts p 
                    LEFT JOIN ' . $prefix . "postmeta pm ON pm.post_id = p.ID AND pm.meta_key = 'WooCommerceEventsEvent'  
                    WHERE pm.meta_value = 'Event' 
                    ORDER BY p.post_title ASC
                "
			);

			require $this->config->template_path . 'ticket-filter-options.php';

		}
	}

	/**
	 * Delete logged check-ins and AddToWallet card before delete ticket
	 *
	 * @param int $post_id post ID.
	 */
	public function delete_tickets_permanently( $post_id ) {

		global $wpdb;

		if ( 'event_magic_tickets' === get_post_type( $post_id ) ) {

			$ticket = $this->get_ticket_data( $post_id );

			if ( ! empty( $ticket ) ) {

				$tid = (int) $post_id;
				$eid = (int) $ticket['WooCommerceEventsProductID'];

				if ( ! empty( $tid ) && ! empty( $eid ) ) {

					$table_name = $wpdb->prefix . 'fooevents_check_in';

					$wpdb->delete(
						$table_name,
						array(
							'tid' => $tid,
							'eid' => $eid,
						),
						array( '%d', '%d' )
					);

				}
			}

			/* Delete AddToWallet Pass */
			$fooevents_class = new FooEvents();
			$fooevents_class->fooevents_wallet_pass( $post_id, 'delete' );
		}
	}

	public function untrash_ticket( $post_id ) {

		$post_type = get_post_type( $post_id );

		if ( 'event_magic_tickets' === $post_type ) {

			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'publish',
				)
			);

		}
	}

	/**
	 * Suppress the order complete notification when ticket is created in the admin.
	 *
	 * @param string   $recipient the recipient.
	 * @param WC_Order $order the WooCommerce order.
	 */
	public function suppress_admin_order_email_notifications( $recipient, $order ) {

		$global_woocommerce_events_suppress_admin_notifications = get_option( 'globalWooCommerceEventsSuppressAdminNotifications' );

		if ( 'yes' === $global_woocommerce_events_suppress_admin_notifications ) {

			$admin_add_ticket = '';
			if ( ! empty( $order ) ) {

				$admin_add_ticket = $order->get_meta( 'WooCommerceEventsOrderAdminAddTicket', true );

			}

			if ( 'yes' === $admin_add_ticket ) {

				return;

			} else {

				return $recipient;

			}
		} else {

			return $recipient;

		}
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

	/**
	 * Generates random string used for ticket hash
	 *
	 * @param int $length length.
	 * @return string
	 */
	private function generate_random_string( $length = 10 ) {

		return substr( str_shuffle( str_repeat( $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );
	}

	/**
	 * Cleans a CSV heading
	 *
	 * @param string $field_name The field name for cleaning.
	 */
	private function process_clean_csv_heading( $field_name ) {

		$field_name = trim( $field_name );
		$field_name = str_replace( ' ', '-', $field_name );
		$field_name = strtolower( $field_name );

		return $field_name;
	}

	/**
	 * Determine if field is custom attendee field
	 *
	 * @param string $field_name The field name.
	 */
	private function is_custom_attendee_field( $field_name ) {

		if ( strpos( $field_name, 'fooevents_custom_' ) === 0 ) {

			return true;

		}
	}

	/**
	 * Vaildate email address
	 *
	 * @param string $email the email address to be validated.
	 */
	private function validate_email_address( $email ) {

		$email = trim( $email );
		return filter_var( $email, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Validate if $id is an event
	 *
	 * @param int $id the product id.
	 */
	private function validate_event( $id ) {

		$id = trim( $id );

		if ( ! is_numeric( $id ) ) {

			return false;

		}

		$event = get_post_meta( $id, 'WooCommerceEventsEvent', true );

		if ( 'Event' !== $event ) {

			return false;

		}

		return true;
	}

	/**
	 * Validate if alphanumeric
	 *
	 * @param string $string the string to be validated.
	 */
	private function validate_alphanumeric( $string ) {

		$string = trim( $string );

		if ( ! preg_match( '/^[a-z0-9 .\-]+$/i', $string ) ) {

			return false;

		}

		return true;
	}

	/**
	 * Validate if numeric
	 *
	 * @param string $string the string to be validated
	 */
	private function validate_numeric( $string ) {

		$string = trim( $string );

		if ( ! is_numeric( $string ) ) {

			return false;

		}
	}

	/**
	 * Array of month names for translation to English
	 *
	 * @param string $event_date event date.
	 * @return string
	 */
	private function convert_month_to_english( $event_date ) {

		$months = array(
			// French.
			'janvier'     => 'January',
			'février'     => 'February',
			'mars'        => 'March',
			'avril'       => 'April',
			'mai'         => 'May',
			'juin'        => 'June',
			'juillet'     => 'July',
			'aout'        => 'August',
			'août'        => 'August',
			'septembre'   => 'September',
			'octobre'     => 'October',

			// German.
			'Januar'      => 'January',
			'Februar'     => 'February',
			'März'        => 'March',
			'Mai'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Oktober'     => 'October',
			'Dezember'    => 'December',
			'Montag'      => '',
			'Dienstag'    => '',
			'Mittwoch'    => '',
			'Donnerstag'  => '',
			'Freitag'     => '',
			'Samstag'     => '',
			'Sonntag'     => '',

			// Spanish.
			'enero'       => 'January',
			'febrero'     => 'February',
			'marzo'       => 'March',
			'abril'       => 'April',
			'mayo'        => 'May',
			'junio'       => 'June',
			'julio'       => 'July',
			'agosto'      => 'August',
			'septiembre'  => 'September',
			'setiembre'   => 'September',
			'octubre'     => 'October',
			'noviembre'   => 'November',
			'diciembre'   => 'December',
			'novembre'    => 'November',
			'décembre'    => 'December',
			'lunes'       => '',
			'martes'      => '',
			'miércoles'   => '',
			'jueves'      => '',
			'viernes'     => '',
			'sábado'      => '',
			'domingo'     => '',

			// Catalan - Spain.
			'gener'       => 'January',
			'febrer'      => 'February',
			'març'        => 'March',
			'abril'       => 'April',
			'maig'        => 'May',
			'juny'        => 'June',
			'juliol'      => 'July',
			'agost'       => 'August',
			'setembre'    => 'September',
			'octubre'     => 'October',
			'novembre'    => 'November',
			'desembre'    => 'December',

			// Dutch.
			'januari'     => 'January',
			'februari'    => 'February',
			'maart'       => 'March',
			'april'       => 'April',
			'mei'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'augustus'    => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'maandag'     => '',
			'dinsdag'     => '',
			'woensdag'    => '',
			'donderdag'   => '',
			'vrijdag'     => '',
			'zaterdag'    => '',
			'zondag'      => '',

			// Italian.
			'Gennaio'     => 'January',
			'Febbraio'    => 'February',
			'Marzo'       => 'March',
			'Aprile'      => 'April',
			'Maggio'      => 'May',
			'Giugno'      => 'June',
			'Luglio'      => 'July',
			'Agosto'      => 'August',
			'Settembre'   => 'September',
			'Ottobre'     => 'October',
			'Novembre'    => 'November',
			'Dicembre'    => 'December',

			// Polish.
			'Styczeń'     => 'January',
			'Luty'        => 'February',
			'Marzec'      => 'March',
			'Kwiecień'    => 'April',
			'Maj'         => 'May',
			'Czerwiec'    => 'June',
			'Lipiec'      => 'July',
			'Sierpień'    => 'August',
			'Wrzesień'    => 'September',
			'Październik' => 'October',
			'Listopad'    => 'November',
			'Grudzień'    => 'December',

			// Afrikaans.
			'Januarie'    => 'January',
			'Februarie'   => 'February',
			'Maart'       => 'March',
			'Mei'         => 'May',
			'Junie'       => 'June',
			'Julie'       => 'July',
			'Augustus'    => 'August',
			'Oktober'     => 'October',
			'Desember'    => 'December',

			// Turkish.
			'Ocak'        => 'January',
			'Şubat'       => 'February',
			'Mart'        => 'March',
			'Nisan'       => 'April',
			'Mayıs'       => 'May',
			'Haziran'     => 'June',
			'Temmuz'      => 'July',
			'Ağustos'     => 'August',
			'Eylül'       => 'September',
			'Ekim'        => 'October',
			'Kasım'       => 'November',
			'Aralık'      => 'December',

			// Portuguese.
			'janeiro'     => 'January',
			'fevereiro'   => 'February',
			'março'       => 'March',
			'abril'       => 'April',
			'maio'        => 'May',
			'junho'       => 'June',
			'julho'       => 'July',
			'agosto'      => 'August',
			'setembro'    => 'September',
			'outubro'     => 'October',
			'novembro'    => 'November',
			'dezembro'    => 'December',

			// Swedish.
			'Januari'     => 'January',
			'Februari'    => 'February',
			'Mars'        => 'March',
			'April'       => 'April',
			'Maj'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Augusti'     => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'November'    => 'November',
			'December'    => 'December',

			// Czech.
			'leden'       => 'January',
			'únor'        => 'February',
			'březen'      => 'March',
			'duben'       => 'April',
			'květen'      => 'May',
			'červen'      => 'June',
			'červenec'    => 'July',
			'srpen'       => 'August',
			'září'        => 'September',
			'říjen'       => 'October',
			'listopad'    => 'November',
			'prosinec'    => 'December',

			// Norwegian.
			'januar'      => 'January',
			'februar'     => 'February',
			'mars'        => 'March',
			'april'       => 'April',
			'mai'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'desember'    => 'December',

			// Danish.
			'januar'      => 'January',
			'februar'     => 'February',
			'marts'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Finnish.
			'tammikuu'    => 'January',
			'helmikuu'    => 'February',
			'maaliskuu'   => 'March',
			'huhtikuu'    => 'April',
			'toukokuu'    => 'May',
			'kesäkuu'     => 'June',
			'heinäkuu'    => 'July',
			'elokuu'      => 'August',
			'syyskuu'     => 'September',
			'lokakuu'     => 'October',
			'marraskuu'   => 'November',
			'joulukuu'    => 'December',

			// Russian.
			'Январь'      => 'January',
			'Февраль'     => 'February',
			'Март'        => 'March',
			'Апрель'      => 'April',
			'Май'         => 'May',
			'Июнь'        => 'June',
			'Июль'        => 'July',
			'Август'      => 'August',
			'Сентябрь'    => 'September',
			'Октябрь'     => 'October',
			'Ноябрь'      => 'November',
			'Декабрь'     => 'December',

			// Icelandic.
			'Janúar'      => 'January',
			'Febrúar'     => 'February',
			'Mars'        => 'March',
			'Apríl'       => 'April',
			'Maí'         => 'May',
			'Júní'        => 'June',
			'Júlí'        => 'July',
			'Ágúst'       => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'Nóvember'    => 'November',
			'Desember'    => 'December',

			// Latvian.
			'janvāris'    => 'January',
			'februāris'   => 'February',
			'marts'       => 'March',
			'aprīlis'     => 'April',
			'maijs'       => 'May',
			'jūnijs'      => 'June',
			'jūlijs'      => 'July',
			'augusts'     => 'August',
			'septembris'  => 'September',
			'oktobris'    => 'October',
			'novembris'   => 'November',
			'decembris'   => 'December',

			// Lithuanian.
			'sausio'      => 'January',
			'vasario'     => 'February',
			'kovo'        => 'March',
			'balandžio'   => 'April',
			'gegužės'     => 'May',
			'birželio'    => 'June',
			'liepos'      => 'July',
			'rugpjūčio'   => 'August',
			'rugsėjo'     => 'September',
			'spalio'      => 'October',
			'lapkričio'   => 'November',
			'gruodžio'    => 'December',

			// Estonian.
			'jaanuar'     => 'January',
			'veebruar'    => 'February',
			'märts'       => 'March',
			'aprill'      => 'April',
			'mai'         => 'May',
			'juuni'       => 'June',
			'juuli'       => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktoober'    => 'October',
			'november'    => 'November',
			'detsember'   => 'December',

			// Greek.
			'Ιανουάριος'  => 'January',
			'Φεβρουάριος' => 'February',
			'Μάρτιος'     => 'March',
			'Απρίλιος'    => 'April',
			'Μάιος'       => 'May',
			'Ιούνιος'     => 'June',
			'Ιούλιος'     => 'July',
			'Αύγουστος'   => 'August',
			'Σεπτέμβριος' => 'September',
			'Οκτώβριος'   => 'October',
			'Νοέμβριος'   => 'November',
			'Δεκέμβριος'  => 'December',

			// Slovak - Slovakia.
			'január'      => 'January',
			'február'     => 'February',
			'marec'       => 'March',
			'apríl'       => 'April',
			'máj'         => 'May',
			'jún'         => 'June',
			'júl'         => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'október'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Slovenian - Slovenia.
			'januar'      => 'January',
			'februar'     => 'February',
			'marec'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'junij'       => 'June',
			'julij'       => 'July',
			'avgust'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Romanian - Romania.
			'ianuarie'    => 'January',
			'februarie'   => 'February',
			'martie'      => 'March',
			'aprilie'     => 'April',
			'mai'         => 'May',
			'iunie'       => 'June',
			'iulie'       => 'July',
			'august'      => 'August',
			'septembrie'  => 'September',
			'octombrie'   => 'October',
			'noiembrie'   => 'November',
			'decembrie'   => 'December',

			// Croatian - Croatia.

			'siječanj'    => 'January',
			'veljača'     => 'February',
			'ožujak'      => 'March',
			'travanj'     => 'April',
			'svibanj'     => 'May',
			'lipanj'      => 'June',
			'srpanj'      => 'July',
			'kolovoz'     => 'August',
			'rujan'       => 'September',
			'listopad'    => 'October',
			'studeni'     => 'November',
			'prosinac'    => 'December',

			// Bulgarian - Bulgaria.

			'януари'      => 'January',
			'февруари'    => 'February',
			'март'        => 'March',
			'април'       => 'April',
			'май'         => 'May',
			'юни'         => 'June',
			'юли'         => 'July',
			'август'      => 'August',
			'септември'   => 'September',
			'октомври'    => 'October',
			'ноември'     => 'November',
			'декември'    => 'December',
		);

		$pattern     = array_keys( $months );
		$replacement = array_values( $months );

		foreach ( $pattern as $key => $value ) {
			$pattern[ $key ] = '/\b' . $value . '\b/iu';
		}

		$replaced_event_date = preg_replace( $pattern, $replacement, $event_date );

		$replaced_event_date = str_replace( ' de ', ' ', $replaced_event_date );

		return $replaced_event_date;
	}
}
