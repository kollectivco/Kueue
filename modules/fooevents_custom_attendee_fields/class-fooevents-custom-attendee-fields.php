<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-custom-attendee-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class Fooevents_Custom_Attendee_Fields {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * Update helper object
	 *
	 * @var object $update_helper responsible for plugin updates
	 */
	private $update_helper;

	/**
	 * On plugin load
	 */
	public function __construct() {

		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_custom_attendee_field_options_tab' ), 24 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_custom_attendee_field_options_tab_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ) );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'save_post', array( &$this, 'save_ticket_meta_boxes' ), 1, 2 );
		add_action( 'admin_notices', array( $this, 'check_fooevents' ), 10 );
		add_action( 'wp_ajax_fooevents_fetch_add_ticket_attendee_options', array( $this, 'fetch_add_ticket_attendee_options' ) );

		add_filter( 'woocommerce_form_field_fooeventshidden', array( $this, 'create_checkout_hidden_field' ), 999, 4 );

		$this->plugin_init();
	}

	/**
	 * Initializes plugin
	 */
	public function plugin_init() {

		// Main config.
		$this->config = new FooEvents_Custom_Attendee_Fields_Config();
		// update_helper.
		require_once $this->config->class_path . 'updatehelper.php';
		$this->update_helper = new Fooevents_Custom_Attendee_Fields_Update_Helper( $this->config );
	}

	/**
	 * Initializes the WooCommerce meta box
	 */
	public function add_product_custom_attendee_field_options_tab() {

		echo '<li class="custom_tab_custom_attendee_options"><a href="#fooevents_custom_attendee_field_options">' . esc_attr( __( 'Custom Attendee Fields', 'fooevents-custom-attendee-fields' ) ) . '</a></li>';
	}

	/**
	 * Add custom attendee field tabs
	 */
	public function add_product_custom_attendee_field_options_tab_options() {

		global $post;

		$is_checkout_block_enabled = $this->is_checkout_block_enabled();

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $post->ID, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );

		$fooevents_custom_attendee_fields_options = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		if ( empty( $fooevents_custom_attendee_fields_options ) ) {

			$fooevents_custom_attendee_fields_options = array();

		}

		require $this->config->template_path . 'custom-attendee-fields-options.php';
	}

	/**
	 * Processes the meta box form once the publish / update button is clicked.
	 *
	 * @global object $woocommerce_errors
	 * @param int $post_id post id.
	 */
	public function process_meta_box( $post_id ) {

		global $woocommerce_errors;

		$nonce = '';
		if ( isset( $_POST['fooevents_custom_attendee_fields_nonce'] ) ) {
			$nonce = esc_attr( wp_unslash( $_POST['fooevents_custom_attendee_fields_nonce'] ) );
		}

		/*
		if ( ! wp_verify_nonce( $nonce, 'fooevents_custom_attendee_fields_options' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Custom Attendee Fields 0001', 'fooevents-custom-attendee-fields' ) );
		}*/

		if ( isset( $_POST['fooevents_custom_attendee_fields_options_serialized'] ) ) {

			$fooevents_custom_attendee_fields_options_serialized = $_POST['fooevents_custom_attendee_fields_options_serialized'];
			update_post_meta( $post_id, 'fooevents_custom_attendee_fields_options_serialized', $fooevents_custom_attendee_fields_options_serialized );

		}
	}

	/**
	 * Register plugin scripts.
	 */
	public function register_scripts() {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'fooevents-custom-attendee-fields-script', $this->config->scripts_path . 'attendee-custom-fields.js', array( 'jquery', 'jquery-ui-sortable' ), $this->config->plugin_data['Version'], true );

		if ( isset( $_GET['post_type'] ) && 'event_magic_tickets' === $_GET['post_type'] ) {

			wp_enqueue_script( 'events-attendee-fields-admin-add-ticket', $this->config->scripts_path . 'events-attendee-fields-admin-add-ticket.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'events-attendee-fields-admin-add-ticket', 'FooEventsAttendeeAddTicketObj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}
	}

	/**
	 * Register plugin styles.
	 */
	public function register_styles() {

		wp_enqueue_style( 'fooevents-custom-attendee-fields-style', $this->config->styles_path . 'attendee-custom-fields.css', array(), $this->config->plugin_data['Version'] );
	}

	/**
	 * Checks if FooEvents is installed
	 */
	public function check_fooevents() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( ! is_plugin_active( 'fooevents/fooevents.php' ) ) {

				$this->output_notices( array( __( 'The FooEvents Custom Attendee Fields plugin requires FooEvents for WooCommerce to be installed.', 'fooevents-express-check-in' ) ) );

		}
	}

	/**
	 * Outputs custom attendee fields on the checkout screen
	 *
	 * @param int    $product_id the product ID.
	 * @param int    $x event counter.
	 * @param int    $y ticket counter.
	 * @param array  $ticket ticket.
	 * @param object $checkout checkout object.
	 */
	public function output_attendee_fields( $product_id, $x, $y, $ticket, $checkout ) {

		global $woocommerce;

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$allowed_html = wp_kses_allowed_html( 'post' );

		$attendee_term = get_post_meta( $ticket['product_id'], 'WooCommerceEventsAttendeeOverride', true );

		if ( empty( $attendee_term ) ) {

			$attendee_term = get_option( 'globalWooCommerceEventsAttendeeOverride', true );

		}

		if ( empty( $attendee_term ) || 1 === $attendee_term ) {

			$attendee_term = __( 'Attendee', 'fooevents-custom-attendee-fields' );

		}

		if ( ! empty( $fooevents_custom_attendee_fields_options ) ) {

			$z = 1;

			foreach ( $fooevents_custom_attendee_fields_options as $option_key => $option ) {

				$required                            = ( 'true' === $option[ $option_key . '_req' ] ) ? true : false;
				$option_label_output                 = wp_kses( $option[ $option_key . '_label' ], $allowed_html );
				$text_label                          = $option_label_output;
				$option_label_output_encoded         = $this->encode_custom_field_name( $option[ $option_key . '_label' ] );
				$global_woocommerce_use_placeholders = get_option( 'globalWooCommerceUsePlaceHolders', true );
				$field_params                        = array();
				$field_params2                       = array();
				$value                               = '';

				if ( 'text' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'text',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'textarea' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'textarea',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'select' === $option[ $option_key . '_type' ] ) {

					$select_values = array();
					if ( ! empty( $option[ $option_key . '_options' ] ) ) {

						$select_options = explode( '|', $option[ $option_key . '_options' ] );

						foreach ( $select_options as $select_option ) {

							$select_option                   = trim( $select_option );
							$select_values[ $select_option ] = $select_option;

						}
					}

					if ( ! empty( $option[ $option_key . '_def' ] ) ) {

						$select_values = array( '' => $option[ $option_key . '_def' ] ) + $select_values;

					}

					$field_params = array(
						'type'        => 'select',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'options'     => $select_values,
						'required'    => $required,
						'default'     => $option[ $option_key . '_def' ],
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'checkbox' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'checkbox',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					$field_params2 = array(
						'type'        => 'fooeventshidden',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'radio' === $option[ $option_key . '_type' ] ) {

					$value = $checkout->get_value( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y ) ? $checkout->get_value( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y ) : $option[ $option_key . '_def' ];

					$select_values = array();
					if ( ! empty( $option[ $option_key . '_options' ] ) ) {

						$select_options = explode( '|', $option[ $option_key . '_options' ] );

						foreach ( $select_options as $select_option ) {

							$select_option                   = trim( $select_option );
							$select_values[ $select_option ] = $select_option;

						}
					}

					if ( ! empty( $option[ $option_key . '_def' ] ) ) {

						$select_values = array_merge( array( $option[ $option_key . '_def' ] => $option[ $option_key . '_def' ] ), $select_values );

					}

					$field_params = array(
						'type'        => 'radio',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'options'     => $select_values,
						'required'    => $required,
						'default'     => $option[ $option_key . '_def' ],
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'country' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'country',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'date' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'date',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'time' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'time',
						'class'       => array( 'attendee-class form-row-wide' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'email' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'        => 'text',
						'class'       => array( 'attendee-class', 'form-row-wide', 'fooevents-email' ),
						'label'       => $option_label_output,
						'placeholder' => '',
						'required'    => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'url' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'          => 'text',
						'class'         => array( 'attendee-class form-row-wide' ),
						'label'         => $option_label_output,
						'placeholder'   => '',
						'fooevents-val' => 'fooevents-url',
						'required'      => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'numbers' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'          => 'number',
						'class'         => array( 'attendee-class form-row-wide' ),
						'label'         => $option_label_output,
						'placeholder'   => '',
						'fooevents-val' => 'fooevents-numbers',
						'required'      => $required,
					);

					$value = 0;

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'alphabet' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'          => 'text',
						'class'         => array( 'attendee-class form-row-wide' ),
						'label'         => $option_label_output,
						'placeholder'   => '',
						'fooevents-val' => 'fooevents-alphabet',
						'required'      => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				} elseif ( 'alphanumeric' === $option[ $option_key . '_type' ] ) {

					$field_params = array(
						'type'          => 'text',
						'class'         => array( 'attendee-class form-row-wide' ),
						'label'         => $option_label_output,
						'placeholder'   => '',
						'fooevents-val' => 'fooevents-alphanumeric',
						'required'      => $required,
					);

					if ( 'yes' === $global_woocommerce_use_placeholders ) {

						$field_params['placeholder'] = $text_label;

					}
				}

				if ( ! empty( $field_params2 ) ) {

					woocommerce_form_field( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y, $field_params2, $checkout->get_value( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y ) );

				}

				if ( empty( $value ) && $checkout->get_value( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y ) ) {

					$value = $checkout->get_value( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y );

				}

				if ( ! empty( $field_params ) ) {

					woocommerce_form_field( 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y, $field_params, $value );

				}

				++$z;

			}

			wp_nonce_field( 'fooevents_custom_attendee_fields_checkout', 'fooevents_custom_attendee_fields_checkout_nonce' );

		}
	}

	/**
	 * Filter to create hidden fields on checkout
	 *
	 * @param int    $no_parameter integer.
	 * @param string $key string.
	 * @param array  $args array.
	 * @param string $value value.
	 */
	public function create_checkout_hidden_field( $no_parameter, $key, $args, $value ) {

		if ( isset( $args['id'] ) && strpos( $args['id'], 'fooevents_custom_' ) === 0 ) {

			$field = '<p class="form-row ' . implode( ' ', $args['class'] ) . '" id="' . $key . '_field">
            <input type="hidden" class="input-hidden" name="' . $key . '" id="' . $key . '" placeholder="' . $args['placeholder'] . '" value="0" />
            </p>';

			return $field;
		}
	}

	/**
	 * Validate bookings selection on add ticket page
	 *
	 * @param int   $event_id event ID.
	 * @param array $fields array of custom attendee fields.
	 */
	public function admin_add_ticket_custom_fields_validate( $event_id, $fields ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $event_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		foreach ( $fields as $key => $value ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				} elseif ( isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				}

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) && 'true' === $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_req' ] ) {

					if ( empty( $fields[ $key ] ) ) {

						$message = sprintf( __( '%s is a required field.', 'fooevents-custom-attendee-fields' ), $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ] );
						return wp_json_encode(
							array(
								'type'    => 'error',
								'message' => $message,
							)
						);

					}
				}
			}
		}
	}

	/**
	 * Validate bookings selection on add ticket page
	 *
	 * @param int   $event_id event id.
	 * @param array $fields array of fields.
	 */
	public function admin_edit_ticket_custom_fields_validate( $event_id, $fields ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $event_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		foreach ( $fields as $key => $value ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				} elseif ( isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				}

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) && 'true' === $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_req' ] ) {

					if ( empty( $fields[ $key ] ) ) {

						$message = sprintf( __( '%s is a required field.', 'fooevents-custom-attendee-fields' ), $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ] );
						return wp_json_encode(
							array(
								'type'    => 'error',
								'message' => $message,
							)
						);

					}
				}
			}
		}
	}

	/**
	 * Gets custom attendee details to be displayed on the orders page for tickets which have not been generated
	 *
	 * @param int   $product_id product id.
	 * @param array $detected_custom_fields array of detected fields.
	 */
	public function fetch_attendee_details_for_order( $product_id, $detected_custom_fields ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$custom_values = array();

		foreach ( $detected_custom_fields as $key => $value ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				} elseif ( isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				}

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$custom_values[ $field_id ]['name']  = $key;
					$custom_values[ $field_id ]['value'] = esc_attr( $value );
					$custom_values[ $field_id ]['field'] = $fooevents_custom_attendee_fields_options[ $field_id ];

				}
			}
		}

		return $custom_values;
	}

	/**
	 * Gets custom attendee details to be displayed on the orders page for tickets which have  been generated
	 *
	 * @param int $product_id product ID.
	 * @param int $ticket_id ticket ID.
	 */
	public function fetch_attendee_details_for_order_generated( $product_id, $ticket_id ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$custom_values = array();

		$post_meta = get_post_meta( $ticket_id );

		$x = 0;
		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

					// LEGACY: 20201207.
				} elseif ( isset( $field[3] ) && isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				} else {

					$legacy_option = str_replace( 'fooevents_custom_', '', $key );
					$legacy_option = str_replace( '_', ' ', $legacy_option );
					$legacy_option = ucwords( $legacy_option );

					$custom_values[ $x . '_legacy' ]['name']                          = $key;
					$custom_values[ $x . '_legacy' ]['value']                         = $meta[0];
					$custom_values[ $x . '_legacy' ]['field'][ $x . '_legacy_label' ] = $legacy_option;

					++$x;

				}
				// ENDLEGACY: 20201207.

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$custom_values[ $field_id ]['name']  = $key;
					$custom_values[ $field_id ]['value'] = esc_attr( $meta[0] );
					$custom_values[ $field_id ]['field'] = $fooevents_custom_attendee_fields_options[ $field_id ];

				}
			}
		}

		return $custom_values;
	}

	/**
	 * Validates custom attendee details on the checkout page
	 *
	 * @param array $ticket ticket data.
	 * @param int   $event int.
	 * @param int   $x counter.
	 * @param int   $y counter.
	 */
	public function validate_custom_fields( $ticket, $event, $x, $y ) {

		global $woocommerce;

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $ticket['product_id'], 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );
		$event_title = get_the_title( $event );

		if ( ! empty( $fooevents_custom_attendee_fields_options ) ) {

			$z = 1;

			foreach ( $fooevents_custom_attendee_fields_options as $option_key => $option ) {

				$label = $option[ $option_key . '_label' ];

				$field_id = 'fooevents_custom_' . $option_key . '_' . $x . '__' . $y;

				if ( 'true' === $option[ $option_key . '_req' ] ) {

					if ( empty( $_POST[ $field_id ] ) && ( 'numbers' !== $option[ $option_key . '_type' ] || ( 'numbers' === $option[ $option_key . '_type' ] && '0' !== $_POST[ $field_id ] && trim( '' === $_POST[ $field_id ] ) ) ) ) {

						$notice = ucfirst( $option[ $option_key . '_label' ] ) . sprintf( __( ' is required for %1$s attendee %2$d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );
						continue;
					}
				}

				if ( 'email' === $option[ $option_key . '_type' ] ) {

					if ( ! empty( $_POST[ $field_id ] ) && ! $this->is_email_valid( $_POST[ $field_id ] ) ) {

						$notice = sprintf( __( ucfirst( $option[ $option_key . '_label' ] ) . ' is not a valid email address for %s attendee %d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				} elseif ( ( 'url' === $option[ $option_key . '_type' ] && 'true' === $option[ $option_key . '_req' ] ) || ( 'url' === $option[ $option_key . '_type' ] && ! empty( $_POST[ $field_id ] ) ) ) {

					if ( ! filter_var( $_POST[ $field_id ], FILTER_VALIDATE_URL ) ) {

						$notice = sprintf( __( ucfirst( $option[ $option_key . '_label' ] ) . ' is not a valid URL for %s attendee %d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				} elseif ( 'numbers' === $option[ $option_key . '_type' ] ) {

					if ( preg_match( '/[^0-9]/', $_POST[ $field_id ] ) ) {

						$notice = sprintf( __( ucfirst( $option[ $option_key . '_label' ] ) . ' is not a valid number for %s attendee %d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				} elseif ( 'alphabet' === $option[ $option_key . '_type' ] ) {

					if ( preg_match( '/[^A-Za-z]/', $_POST[ $field_id ] ) ) {

						$notice = sprintf( __( ucfirst( $option[ $option_key . '_label' ] ) . ' needs to contain only alphabet characters for %s attendee %d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				} elseif ( 'alphanumeric' === $option[ $option_key . '_type' ] ) {

					if ( preg_match( '/[^A-Za-z0-9]/', $_POST[ $field_id ] ) ) {

						$notice = sprintf( __( ucfirst( $option[ $option_key . '_label' ] ) . ' needs to contain only alphabet or number characters for %s attendee %d', 'fooevents-custom-attendee-fields' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				++$z;

			}
		}
	}

	/**
	 * Display custom attendee fields on tickets.
	 *
	 * @param int $ticket_id ticket ID.
	 * @param int $product_id product ID.
	 * @return array
	 */
	public function display_tickets_meta_custom_options_output( $ticket_id, $product_id ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$post_meta     = get_post_meta( $ticket_id );
		$custom_values = array();

		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				}
				// LEGACY: 20200720
				elseif ( isset( $field[3] ) && isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				}
				// ENDLEGACY: 20200720

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$custom_values[ $field_id ]['name']  = $key;
					$custom_values[ $field_id ]['value'] = esc_attr( $meta[0] );
					$custom_values[ $field_id ]['label'] = $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ];

				}
			}
		}

		return $custom_values;
	}

	/**
	 * Display custom attendee fields on tickets.
	 *
	 * @param int $ticket_id ticket ID.
	 * @param int $product_id product ID.
	 * @return string
	 *
	 * LEGACY: 20200831
	 */
	public function display_tickets_meta_custom_options_output_legacy( $ticket_id, $product_id ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$post_meta = get_post_meta( $ticket_id );
		$output    = '';

		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field    = explode( '_', $key );
				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				}
				// LEGACY: 20200831.
				elseif ( isset( $field[3] ) && isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				}
				// ENDLEGACY: 20200831.

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$custom_values[ $key ] = esc_attr( $meta[0] );
					$output               .= '<tr><td width="160px" valign="top">' . $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ] . ':</td><td valign="top">' . esc_attr( $meta[0] ) . '</td></tr>';

				}
			}
		}
		$output = '<table border="0" cellspacing="0" cellpadding="3" width="100%">' . $output . '</table>';
		return $output;
	}
	// ENDLEGACY: 20200831.

	/**
	 * Displays include custom attendee options
	 *
	 * @param object $post WordPress post object.
	 * @return string
	 */
	public function generate_include_custom_attendee_options( $post ) {

		ob_start();

		$woocommerce_events_include_custom_attendee_details = get_post_meta( $post->ID, 'WooCommerceEventsIncludeCustomAttendeeDetails', true );

		require $this->config->template_path . 'product-custom-attendee-options.php';

		$custom_attendee_options = ob_get_clean();

		return $custom_attendee_options;
	}

	/**
	 * Formats custom attendee fields in array
	 *
	 * @param int $id ID.
	 * @return array
	 */
	public function display_tickets_meta_custom_options_array( $id ) {

		$post_meta = get_post_meta( $id );
		$output    = array();

		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field                   = strtolower( $key );
				$custom_values[ $field ] = $meta[0];
				$output[ $field ]        = $meta[0];

			}
		}

		return $output;
	}

	/**
	 * Formats custom attendee fields for CSV
	 *
	 * @param int $id ID.
	 * @param int $product_id product ID.
	 * @return array
	 */
	public function display_tickets_meta_custom_options_array_csv( $id, $product_id ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$post_meta     = get_post_meta( $id );
		$custom_values = array();

		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				}
				// LEGACY: 20200720.
				elseif ( ! empty( $field[3] ) && isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				} else {

					$legacy_option = str_replace( 'fooevents_custom_', '', $key );
					$legacy_option = str_replace( '_', ' ', $legacy_option );
					$legacy_option = ucwords( $legacy_option );

					$custom_values[ $legacy_option ] = $meta[0];

				}
				// ENDLEGACY: 20200720.

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$option_label                   = ucwords( $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ] );
					$custom_values[ $option_label ] = esc_attr( $meta[0] );

				}
			}
		}

		return $custom_values;
	}

	/**
	 * Formats custom attendee fields for CSV unpaid tickets
	 *
	 * @param array $unpaid_custom_fields array of unpaid custom fields.
	 * @param int   $product_id product ID.
	 * @return array
	 */
	public function display_tickets_meta_custom_options_array_csv_unpaid( $unpaid_custom_fields, $product_id ) {

		$custom_values = array();

		if ( ! empty( $unpaid_custom_fields ) ) {

			$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
			$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
			$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

			foreach ( $unpaid_custom_fields as $key => $unpaid_custom_value ) {

				if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

					$field = explode( '_', $key );

					$field_id = '';
					if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

						$field_id = $field[2];

					}//LEGACY: 20200720.
					elseif ( isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

						$field_id = $field[3] . '_option';

					} else {

						$legacy_option = str_replace( 'fooevents_custom_', '', $key );
						$legacy_option = str_replace( '_', ' ', $legacy_option );
						$legacy_option = ucwords( $legacy_option );

						$custom_values[ $legacy_option ] = $unpaid_custom_value;

					}
					// ENDLEGACY: 20200720.

					if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

						$option_label                   = ucwords( $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_label' ] );
						$custom_values[ $option_label ] = esc_attr( $unpaid_custom_value );

					}
				}
			}
		}

		return $custom_values;
	}

	/**
	 * Captures custom attendee options on checkout.
	 *
	 * @param int $product_id product ID.
	 * @param int $x counter.
	 * @param int $y counter.
	 */
	public function capture_custom_attendee_options( $product_id, $x, $y ) {

		$custom_values = array();

		$nonce = '';
		if ( isset( $_POST['fooevents_custom_attendee_fields_checkout_nonce'] ) ) {
			$nonce = esc_attr( wp_unslash( $_POST['fooevents_custom_attendee_fields_checkout_nonce'] ) );
		}

		/*
		if ( ! wp_verify_nonce( $nonce, 'fooevents_custom_attendee_fields_checkout' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Custom Attendee Fields 0002', 'fooevents-custom-attendee-fields' ) );
		}*/

		foreach ( $_POST as $key => $value ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
				$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
				$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

				$field = explode( '_', $key );
				$field = array_reverse( $field );

				if ( $field[2] == $x && $field[0] == $y ) {

					// LEGACY: 20200720.
					if ( 'option' === $field[3] ) {

						$field[3] = $field[3] . '_' . $field[4];

					}
					// ENDLEGACY: 20200720.

					$field_name                                       = $fooevents_custom_attendee_fields_options[ $field[3] ][ $field[3] . '_label' ];
					$custom_values[ 'fooevents_custom_' . $field[3] ] = sanitize_text_field( $value );

				}
			}
		}

		/*
		echo '<pre>';
			print_r( $_POST );
		echo '</pre>';
		exit();*/

		return $custom_values;
	}

	/**
	 * Capture custom attendee options
	 *
	 * @param int   $post_id post ID.
	 * @param array $woocommerce_events_custom_attendee_fields array of custom attendee fields.
	 */
	public function process_capture_custom_attendee_options( $post_id, $woocommerce_events_custom_attendee_fields ) {

		if ( ! empty( $woocommerce_events_custom_attendee_fields ) ) {
			foreach ( $woocommerce_events_custom_attendee_fields as $key => $value ) {

				if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

					$key   = esc_attr( $key );
					$value = esc_attr( $value );
					update_post_meta( $post_id, $key, $value );

				}
			}
		}
	}

	/**
	 * Outputs custom attendee options in admin
	 *
	 * @param int $ticket_post_id ID.
	 * @param int $product_id product ID.
	 * @return string
	 */
	public function ticket_details_attendee_fields( $ticket_post_id, $product_id ) {

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );

		$post_meta            = get_post_meta( $ticket_post_id );
		$custom_values        = array();
		$custom_values_legacy = array();

		foreach ( $post_meta as $key => $meta ) {

			if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

				$field = explode( '_', $key );

				$field_id = '';
				if ( isset( $fooevents_custom_attendee_fields_options[ $field[2] ] ) ) {

					$field_id = $field[2];

				}
				// LEGACY: 20200720.
				elseif ( isset( $fooevents_custom_attendee_fields_options[ $field[3] . '_option' ] ) ) {

					$field_id = $field[3] . '_option';

				} else {

					$legacy_option = str_replace( 'fooevents_custom_', '', $key );
					$legacy_option = str_replace( '_', ' ', $legacy_option );
					$legacy_option = ucwords( $legacy_option );

					$custom_values_legacy[ $legacy_option ] = $meta[0];

				}
				// ENDLEGACY: 20200720.

				if ( ! empty( $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_def' ] ) && in_array( $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_type' ], array( 'select', 'radio' ), true ) ) {

					$fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_options' ] = $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_def' ] . '|' . $fooevents_custom_attendee_fields_options[ $field_id ][ $field_id . '_options' ];

				}

				if ( isset( $fooevents_custom_attendee_fields_options[ $field_id ] ) ) {

					$custom_values[ $field_id ]['name']  = $key;
					$custom_values[ $field_id ]['value'] = esc_attr( $meta[0] );
					$custom_values[ $field_id ]['field'] = $fooevents_custom_attendee_fields_options[ $field_id ];

				}
			}
		}

		ob_start();

		require $this->config->template_path . 'custom-attendee-ticket-detail.php';

		$custom_options = ob_get_clean();

		return $custom_options;
	}

	/**
	 * Saves tickets meta box settings
	 *
	 * @param int $post_ID post ID.
	 * @global object $post WordPress post object.
	 * @global object $woocommerce WooCommerce object.
	 */
	public function save_ticket_meta_boxes( $post_ID ) {

		global $post;
		global $woocommerce;

		if ( is_object( $post ) && isset( $_POST ) ) {

			if ( 'event_magic_tickets' === $post->post_type ) {

				foreach ( $_POST as $key => $value ) {

					if ( strpos( $key, 'fooevents_custom_' ) === 0 ) {

						$value = sanitize_text_field( $value );
						update_post_meta( $post_ID, $key, $value );

					}
				}
			}
		}
	}

	/**
	 * Loads text-domain for localization
	 */
	public function load_text_domain() {

		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'fooevents-custom-attendee-fields', false, $path );
	}

	/**
	 * Fetch booking options for add ticket page
	 */
	public function fetch_add_ticket_attendee_options() {

		$event_id = sanitize_text_field( $_POST['event_id'] );

		$fooevents_custom_attendee_fields_options_serialized = get_post_meta( $event_id, 'fooevents_custom_attendee_fields_options_serialized', true );
		$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
		$fooevents_custom_attendee_fields_options            = $this->correct_legacy_options( $fooevents_custom_attendee_fields_options );
		$custom_values                                       = array();

		foreach ( $fooevents_custom_attendee_fields_options as $key => $field ) {

			$custom_values[ $key ]['name']  = 'fooevents_custom_' . $key;
			$custom_values[ $key ]['value'] = '';
			$custom_values[ $key ]['field'] = $field;

		}

		require_once $this->config->template_path . 'custom-attendee-fields-add-ticket.php';

		exit();
	}

	/**
	 * Converts legacy custom attendee field options to new format
	 *
	 * @param array $fooevents_custom_attendee_fields_options array of custom fields.
	 * @return array $fooevents_custom_attendee_fields_options
	 */
	public function correct_legacy_options( $fooevents_custom_attendee_fields_options ) {

		$processed_fooevents_custom_attendee_fields_options = array();

		if ( ! empty( $fooevents_custom_attendee_fields_options ) ) {

			$x = 1;
			foreach ( $fooevents_custom_attendee_fields_options as $option_key => $option ) {

				if ( strpos( $option_key, 'option' ) !== false ) {

					if ( isset( $option[ $x . '_label' ] ) ) {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ][ $option_key . '_label' ] = $option[ $x . '_label' ];

					} else {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

					}

					if ( isset( $option[ $x . '_type' ] ) ) {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ][ $option_key . '_type' ] = $option[ $x . '_type' ];

					} else {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

					}

					if ( isset( $option[ $x . '_options' ] ) ) {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ][ $option_key . '_options' ] = $option[ $x . '_options' ];

					} else {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

					}

					if ( isset( $option[ $x . '_def' ] ) ) {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ][ $option_key . '_def' ] = $option[ $x . '_def' ];

					} else {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

					}

					if ( isset( $option[ $x . '_req' ] ) ) {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ][ $option_key . '_req' ] = $option[ $x . '_req' ];

					} else {

						$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

					}

					++$x;

				} else {

					$processed_fooevents_custom_attendee_fields_options[ $option_key ] = $option;

				}
			}
		}

		return $processed_fooevents_custom_attendee_fields_options;
	}

	/**
	 * Test if WooCommerce checkout block is enabled.
	 */
	private function is_checkout_block_enabled() {

		$checkout_page_id = wc_get_page_id( 'checkout' );
		$checkout_content = get_post_field( 'post_content', $checkout_page_id );

		$checkout_block_enabled = false;
		if ( has_block( 'woocommerce/checkout', $checkout_content ) ) {

			$checkout_block_enabled = true;

		}

		return $checkout_block_enabled;
	}

	/**
	 * Checks a string for valid email address
	 *
	 * @param string $email email address.
	 * @return bool
	 */
	private function is_email_valid( $email ) {

		return filter_var( $email, FILTER_VALIDATE_EMAIL )
			&& preg_match( '/@.+\./', $email );
	}

	/**
	 * Formats field name
	 *
	 * @param string $field_name field name.
	 * @return string
	 */
	private function encode_custom_field_name( $field_name ) {

		$remove     = array( ',', '!', '?', "'", '.' );
		$field_name = str_replace( ' ', '_', $field_name );
		$field_name = str_replace( $remove, '', $field_name );
		$field_name = esc_attr( $field_name );

		return $field_name;
	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices to output.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="updated"><p>' . esc_attr( $notice ) . '</p></div>';

		}
	}
}
