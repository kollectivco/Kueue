<?php
/**
 * Payment Method class that adds a new payment method to WooCommerce Payment methods.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class contains payment method funtionality and settings.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_Payment_Method extends WC_Payment_Gateway {
	/**
	 * The FooEvents POS phrases helper.
	 *
	 * @since 1.0.0
	 * @var array $fooeventspos_phrases The current phrases helper array.
	 */
	private $fooeventspos_phrases;

	/**
	 * The tooltip for enabling/disabling the payment method.
	 *
	 * @since 1.0.0
	 * @var array $enable_disable_label The current tooltip for enabling/disabling the payment method.
	 */
	private $enable_disable_label;

	/**
	 * Static factory method to a create payment method.
	 *
	 * @since 1.0.0
	 * @param array $fooeventspos_payment_method_options Key/value pairs for the payment method ID, title, description and enable/disable label.
	 *
	 * @return FooEventsPOS_Payment_Method FooEvents POS payment method.
	 */
	public static function fooeventspos_with_options( $fooeventspos_payment_method_options ) {

		if ( ! isset( $fooeventspos_payment_method_options ) ) {
			return;
		}

		return new self( $fooeventspos_payment_method_options );
	}

	/**
	 * Constructor for the payment method gateway.
	 *
	 * @since 1.0.0
	 * @param array $fooeventspos_payment_method_options Key/value pairs for the payment method ID, title, description and enable/disable label.
	 */
	public function __construct( $fooeventspos_payment_method_options ) {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$this->fooeventspos_phrases     = $fooeventspos_phrases;
		$this->id                   = $fooeventspos_payment_method_options['id'];
		$this->has_fields           = false;
		$this->method_description   = $fooeventspos_payment_method_options['description'];
		$this->enable_disable_label = $fooeventspos_payment_method_options['enable_disable_label'];
		$this->icon                 = plugins_url( 'admin/images/' . str_replace( 'fooeventspos-', '', $fooeventspos_payment_method_options['id'] ) . '.svg', __DIR__ );

		if ( '' !== $this->get_option( 'title', '' ) ) {
			$this->method_title = $this->get_option( 'title' );
		} else {
			$this->method_title = $fooeventspos_payment_method_options['title'];
		}

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->method_title;
		$this->availability = 'fooeventspos_app';
		$this->description  = $this->get_option( 'description' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialise Gateway Settings the 'Other Payment Method' Form Fields.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() { // phpcs:ignore FooEvents-POS.NamingConventions.ValidFunctionName.FunctionNamePrefixInvalid

		$this->form_fields = array(
			'enabled' => array(
				'title'   => $this->fooeventspos_phrases['title_payment_method_enable_disable'],
				'type'    => 'checkbox',
				'label'   => $this->enable_disable_label,
				'default' => 'yes',
			),
			'title'   => array(
				'title'       => $this->fooeventspos_phrases['title_payment_method_custom_title'],
				'type'        => 'text',
				'description' => $this->fooeventspos_phrases['title_payment_method_custom_title_tooltip'],
				'default'     => '',
				'desc_tip'    => true,
			),
		);
	}
}
