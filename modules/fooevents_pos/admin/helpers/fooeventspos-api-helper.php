<?php
/**
 * API helper functions used by the REST API and XML-RPC classes.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/helpers
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get all tax rates.
 *
 * @since 1.0.0
 *
 * @return array Tax rates.
 */
function fooeventspos_do_get_all_tax_rates() {

	$wc_tax = new WC_Tax();

	$tax_classes_temp = $wc_tax->get_tax_classes();

	$tax_classes = array_merge( array( '' ), $tax_classes_temp );

	$tax_rates = array();

	foreach ( $tax_classes as $tax_class ) {
		$rates = $wc_tax->get_rates_for_tax_class( $tax_class );

		foreach ( $rates as $rate ) {
			$tax_rates[] = array(
				'trid'  => $rate->tax_rate_id,
				'trc'   => $rate->tax_rate_country,
				'trs'   => $rate->tax_rate_state,
				'trpc'  => isset( $rate->postcode ) ? implode( ';', $rate->postcode ) : '',
				'trn'   => $rate->tax_rate_name,
				'trr'   => $rate->tax_rate,
				'trp'   => $rate->tax_rate_priority,
				'trcm'  => $rate->tax_rate_compound,
				'trsh'  => $rate->tax_rate_shipping,
				'tro'   => $rate->tax_rate_order,
				'trcl'  => '' !== $rate->tax_rate_class ? $rate->tax_rate_class : 'standard',
				'trpcc' => $rate->postcode_count,
				'trcc'  => $rate->city_count,
			);
		}
	}

	return $tax_rates;
}

/**
 * Get payment methods.
 *
 * @since 1.0.0
 * @param bool $admin Specify whether the returned result will only be used for display purposes in the admin area.
 *
 * @return array Payment methods.
 */
function fooeventspos_do_get_all_payment_methods( $admin = false ) {

	$payment_methods = array();

	$payment_gateways = $admin ? WC()->payment_gateways->payment_gateways() : WC()->payment_gateways->get_available_payment_gateways();

	// Loop through Woocommerce available payment gateways.
	foreach ( $payment_gateways as $gateway_id => $gateway ) {
		if ( 'fooeventspos_app' === $gateway->availability ) {
			$payment_method_key = str_replace( '-', '_', $gateway->id );

			if ( $admin ) {
				$payment_methods[ $payment_method_key ] = $gateway->method_title;

				if ( 'fooeventspos_square_reader' === $payment_method_key ) {
					$payment_methods['fooeventspos_square'] = $gateway->method_title;
				}
			} else {
				$payment_methods[] = array(
					'pmk' => $payment_method_key,
					'pmt' => $gateway->method_title,
				);
			}
		}
	}

	return $payment_methods;
}

/**
 * Get payment method value from payment method key.
 *
 * @since 1.0.0
 * @param string $payment_method_key The FooEvents POS payment method key for which to return the payment method domain.
 *
 * @return string Payment method.
 */
function fooeventspos_get_wc_payment_method_from_fooeventspos_key( $payment_method_key ) {

	$payment_gateways = WC()->payment_gateways->payment_gateways();

	foreach ( $payment_gateways as $gateway_id => $gateway ) {
		if ( 'fooeventspos_app' === $gateway->availability ) {
			$method_key = str_replace( '-', '_', $gateway->id );

			if ( $method_key === $payment_method_key ) {
				return $gateway->id;
			}
		}
	}

	return '';
}

/**
 * Format a sale price for display.
 *
 * @since  1.40.5
 * @param  string $regular_price Regular price.
 * @param  string $sale_price    Sale price.
 * @return string
 */
function fooeventspos_format_sale_price( $regular_price, $sale_price ) {
	// Format the prices.
	$formatted_regular_price = is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price;
	$formatted_sale_price    = is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price;

	// Strikethrough pricing.
	$price = '<del>' . $formatted_regular_price . '</del> ';

	// Add the sale price.
	$price .= '<ins>' . $formatted_sale_price . '</ins>';

	return apply_filters( 'woocommerce_format_sale_price', $price, $regular_price, $sale_price );
}

/**
 * Format a price range for display.
 *
 * @since 1.10.5
 * @param  string $from Price from.
 * @param  string $to   Price to.
 * @return string
 */
function fooeventspos_format_price_range( $from, $to ) {
	/* translators: 1: price from 2: price to */
	$price = sprintf( _x( '%1$s <span>&ndash;</span> %2$s', 'Price range: from-to', 'woocommerce' ), is_numeric( $from ) ? wc_price( $from ) : $from, is_numeric( $to ) ? wc_price( $to ) : $to );

	return apply_filters( 'woocommerce_format_price_range', $price, $from, $to );
}

/**
 * Get product price display HTML.
 *
 * @since 1.8.8
 * @param WC_Product $wc_product The WooCommerce product whose price should be formatted in HTML.
 *
 * @return string The formatted product price HTML.
 */
function fooeventspos_get_price_html( $wc_product ) {
	$price = '';

	if ( $wc_product->is_type( 'variable' ) ) {
		$prices = $wc_product->get_variation_prices( true );

		if ( empty( $prices['price'] ) ) {
			$price = apply_filters( 'woocommerce_variable_empty_price_html', '', $wc_product );
		} else {
			$min_price     = current( $prices['price'] );
			$max_price     = end( $prices['price'] );
			$min_reg_price = current( $prices['regular_price'] );
			$max_reg_price = end( $prices['regular_price'] );

			if ( $min_price !== $max_price ) {
				$price = fooeventspos_format_price_range( $min_price, $max_price );
			} elseif ( $wc_product->is_on_sale() && $min_reg_price === $max_reg_price ) {
				$price = fooeventspos_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
			} else {
				$price = wc_price( $min_price );
			}

			$price = apply_filters( 'woocommerce_variable_price_html', $price . $wc_product->get_price_suffix(), $wc_product );
		}
	} elseif ( '' === $wc_product->get_price() ) {
		$price = apply_filters( 'woocommerce_empty_price_html', '', $wc_product );
	} elseif ( $wc_product->is_on_sale() ) {
		$price = fooeventspos_format_sale_price( wc_get_price_to_display( $wc_product, array( 'price' => $wc_product->get_regular_price() ) ), wc_get_price_to_display( $wc_product ) ) . $wc_product->get_price_suffix();
	} else {
		$price = wc_price( wc_get_price_to_display( $wc_product ) ) . $wc_product->get_price_suffix();
	}

	return apply_filters( 'woocommerce_get_price_html', $price, $wc_product );
}

/**
 * Get product price excluding tax.
 *
 * @since 1.0.0
 * @param WC_Product $wc_product The WooCommerce product.
 * @param array      $args Additional arguments.
 * @param string     $type The type of product.
 *
 * @return string Price excluding tax.
 */
function fooeventspos_get_price_excluding_tax( $wc_product, $args = array(), $type = '' ) {
	$price = $wc_product->get_price();

	if ( 'regular' === $type ) {
		$price = $wc_product->get_regular_price();
	} elseif ( 'sale' === $type ) {
		$price = $wc_product->get_sale_price();
	}

	$args = wp_parse_args(
		$args,
		array(
			'qty'   => '',
			'price' => '',
		)
	);

	$price = '' !== $args['price'] ? max( 0.0, (float) $args['price'] ) : $price;
	$qty   = '' !== $args['qty'] ? max( 0.0, (float) $args['qty'] ) : 1;

	if ( '' === $price ) {
		return '';
	} elseif ( empty( $qty ) ) {
		return '0.0';
	}

	$line_price = $price * $qty;

	if ( $wc_product->is_taxable() && wc_prices_include_tax() ) {
		$tax_rates      = WC_Tax::get_rates( $wc_product->get_tax_class() );
		$base_tax_rates = WC_Tax::get_base_tax_rates( $wc_product->get_tax_class( 'unfiltered' ) );
		$remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
		$return_price   = $line_price - array_sum( $remove_taxes ); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
	} else {
		$return_price = $line_price;
	}

	return apply_filters( 'woocommerce_get_price_excluding_tax', $return_price, $qty, $wc_product );
}

/**
 * Get product price including tax.
 *
 * @since 1.0.0
 * @param WC_Product $wc_product The WooCommerce product.
 * @param string     $type The type of product.
 *
 * @return string Price including tax.
 */
function fooeventspos_get_price_including_tax( $wc_product, $type = '' ) {
	$price = $wc_product->get_price();

	if ( 'regular' === $type ) {
		$price = $wc_product->get_regular_price();
	} elseif ( 'sale' === $type ) {
		$price = $wc_product->get_sale_price();
	}

	$qty = 1;

	if ( '' === $price ) {
		return '';
	} elseif ( empty( $qty ) ) {
		return '0.0';
	}

	$line_price   = $price * $qty;
	$return_price = $line_price;

	if ( $wc_product->is_taxable() ) {
		if ( ! wc_prices_include_tax() ) {
			$tax_rates = WC_Tax::get_rates( $wc_product->get_tax_class() );
			$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );

			$taxes_total = array_sum( $taxes );

			$return_price = $line_price + $taxes_total;
		} else {
			$tax_rates      = WC_Tax::get_rates( $wc_product->get_tax_class() );
			$base_tax_rates = WC_Tax::get_base_tax_rates( $wc_product->get_tax_class( 'unfiltered' ) );

			/**
			 * If the customer is excempt from VAT, remove the taxes here.
			 * Either remove the base or the user taxes depending on woocommerce_adjust_non_base_location_prices setting.
			 */
			if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) { // @codingStandardsIgnoreLine.
				$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );

				$remove_taxes_total = array_sum( $remove_taxes );

				$return_price = $line_price - $remove_taxes_total;

				/**
			 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
			 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
			 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
			 */
			} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
				$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
				$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );

				$base_taxes_total   = array_sum( $base_taxes );
				$modded_taxes_total = array_sum( $modded_taxes );

				$return_price = $line_price - $base_taxes_total + $modded_taxes_total;
			}
		}
	}

	return apply_filters( 'woocommerce_get_price_including_tax', $return_price, $qty, $wc_product );
}

/**
 * Get payment method value from key.
 *
 * @since 1.0.0
 * @param string $payment_method_key The key of the payment method.
 *
 * @return string Payment method value.
 */
function fooeventspos_get_payment_method_from_key( $payment_method_key = '' ) {

	require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

	$payment_methods = fooeventspos_do_get_all_payment_methods( true );

	return ! empty( $payment_methods[ $payment_method_key ] ) ? $payment_methods[ $payment_method_key ] : $fooeventspos_phrases[ 'title_payment_method_' . str_replace( 'fooeventspos_', '', $payment_method_key ) ];
}

/**
 * Get payment method key from value.
 *
 * @since 1.0.0
 * @param string $payment_method The payment method.
 *
 * @return string Payment method key.
 */
function fooeventspos_get_payment_method_key_from_value( $payment_method = '' ) {
	$payment_method_key = '';

	if ( 'Split Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_split';
	} elseif ( 'Cash Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_cash';
	} elseif ( 'Card Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_card';
	} elseif ( 'Direct Bank Transfer' === $payment_method ) {
		$payment_method_key = 'fooeventspos_direct_bank_transfer';
	} elseif ( 'Check Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_check_payment';
	} elseif ( 'Cash on Delivery' === $payment_method ) {
		$payment_method_key = 'fooeventspos_cash_on_delivery';
	} elseif ( 'Square Manual Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_square_manual';
	} elseif ( 'Square Terminal Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_square_terminal';
	} elseif ( 'Square' === $payment_method || 'Square Reader Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_square_reader';
	} elseif ( 'Stripe Manual Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_stripe_manual';
	} elseif ( 'Stripe Reader Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_stripe_reader';
	} elseif ( 'Stripe BBPOS Chipper Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_stripe_chipper';
	} elseif ( 'Stripe BBPOS WisePad Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_stripe_wisepad';
	} elseif ( 'Stripe Reader M2 Payment' === $payment_method ) {
		$payment_method_key = 'fooeventspos_stripe_reader_m2';
	} elseif ( 'Other Payment Method' === $payment_method ) {
		$payment_method_key = 'fooeventspos_other';
	} else {
		$payment_methods = fooeventspos_do_get_all_payment_methods( true );

		foreach ( $payment_methods as $temp_payment_method_key => $payment_method_value ) {
			if ( $payment_method_value === $payment_method ) {
				$payment_method_key = $temp_payment_method_key;

				break;
			}
		}
	}

	return $payment_method_key;
}

/**
 * Sorting function for product categories.
 *
 * @since 1.0.0
 * @param array $a The one category object to compare.
 * @param array $b The other category object to compare.
 *
 * @return bool String compare result.
 */
function fooeventspos_do_compare_categories( $a, $b ) {
	return strcmp( $a['pcn'], $b['pcn'] );
}

/**
 * Get all product categories.
 *
 * @since 1.0.0
 *
 * @return array Product categories.
 */
function fooeventspos_do_get_all_product_categories() {

	$categories_to_display = array();

	if ( 'cat' === (string) get_option( 'globalFooEventsPOSProductsToDisplay', '' ) ) {
		$categories_to_display = get_option( 'globalFooEventsPOSProductCategories', array() );
	}

	$cats = get_terms( 'product_cat' );

	$temp_categories = array();

	foreach ( $cats as $cat ) {
		$category = array();

		$category['pcid'] = (string) $cat->term_id;

		if ( ! empty( $categories_to_display ) && ! in_array( $category['pcid'], $categories_to_display, true ) ) {
			continue;
		}

		$temp_display_name = '';

		if ( $cat->parent > 0 ) {
			foreach ( $cats as $parent_cat ) {
				if ( $parent_cat->term_id === $cat->parent ) {
					$temp_display_name .= html_entity_decode( $parent_cat->name, ENT_QUOTES, 'UTF-8' ) . ' - ';

					break;
				}
			}
		}

		$temp_display_name .= html_entity_decode( $cat->name, ENT_QUOTES, 'UTF-8' );

		$category['pcn'] = (string) $temp_display_name;

		$temp_categories[] = $category;

		$category          = null;
		$temp_display_name = null;

		unset( $category, $temp_display_name );
	}

	uasort( $temp_categories, 'fooeventspos_do_compare_categories' );

	$categories = array();

	foreach ( $temp_categories as $key => $category ) {
		$categories[] = $category;
	}

	$cats            = null;
	$temp_categories = null;

	unset( $cats, $temp_categories );

	return $categories;
}

/**
 * Fetch all product images.
 *
 * @since 1.0.0
 * @param int    $offset The offset from where to start adding fetched product images.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Product images.
 */
function fooeventspos_do_get_all_product_images( $offset = 0, $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$product_image_data = array();

	$load_product_images = get_option( 'globalFooEventsPOSProductsLoadImages', 'yes' );

	if ( '' === $load_product_images ) {

		$product_image_data['product_images'] = array();

		return $product_image_data;
	}

	$product_data = array();

	$max_products = get_option( 'globalFooEventsPOSProductsPerPage', '500' );

	$product_statuses = get_option( 'globalFooEventsPOSProductsStatus', array( 'publish' ) );

	if ( empty( $product_statuses ) ) {
		$product_statuses = array( 'publish' );
	}

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => $max_products,
		'offset'         => $offset * $max_products,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_status'    => $product_statuses,
	);

	if ( 'cat' === (string) get_option( 'globalFooEventsPOSProductsToDisplay', '' ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'product_cat',
				'terms'    => get_option( 'globalFooEventsPOSProductCategories' ),
				'operator' => 'IN',
			),
		);
	}

	$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery

	if ( 'yes' === (string) get_option( 'globalFooEventsPOSProductsOnlyInStock', '' ) ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		);
	}

	$query = new WP_Query( $args );

	$args['posts_per_page'] = -1;
	$args['offset']         = 0;

	$count_query = new WP_Query( $args );

	$args = null;

	unset( $args );

	$product_image_data['total_product_images'] = $count_query->post_count . '_total_product_images';

	$count_query = null;

	unset( $count_query );

	$product_image_data['product_images'] = array();

	foreach ( $query->posts as $product_id ) {
		$product_image = preg_replace_callback(
			'/[^\x20-\x7f]/',
			function ( $product_image_matches ) {
				return rawurlencode( $product_image_matches[0] );
			},
			(string) get_the_post_thumbnail_url( $product_id, 'thumbnail' )
		);

		$product_image_data['product_images'][] = array(
			'pid' => (string) $product_id,
			'pi'  => $product_image,
		);

	}

	$query = null;

	unset( $query );

	return $product_image_data;
}

/**
 * Fetch all products.
 *
 * @since 1.0.0
 * @param int    $offset The offset from where to start adding fetched products.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Products.
 */
function fooeventspos_do_get_all_products( $offset = 0, $platform = 'any' ) {

	$product_data = array();

	$max_products = get_option( 'globalFooEventsPOSProductsPerPage', '500' );

	$product_statuses = get_option( 'globalFooEventsPOSProductsStatus', array( 'publish' ) );

	if ( empty( $product_statuses ) ) {
		$product_statuses = array( 'publish' );
	}

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => $max_products,
		'offset'         => $offset * $max_products,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_status'    => $product_statuses,
	);

	if ( 'cat' === (string) get_option( 'globalFooEventsPOSProductsToDisplay', '' ) ) {
		$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'product_cat',
				'terms'    => get_option( 'globalFooEventsPOSProductCategories' ),
				'operator' => 'IN',
			),
		);
	}

	$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery

	if ( 'yes' === (string) get_option( 'globalFooEventsPOSProductsOnlyInStock', '' ) ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		);
	}

	$query = new WP_Query( $args );

	if ( 0 === $offset ) {
		$args['posts_per_page'] = -1;
		$args['offset']         = 0;

		$count_query = new WP_Query( $args );

		$product_data['total_products'] = $count_query->post_count . '_total_products';
	}

	$args = null;

	unset( $args );

	$count_query = null;

	unset( $count_query );

	$product_data['sale_product_ids'] = '';

	if ( 0 === $offset ) {
		$product_data['sale_product_ids'] = implode( ',', wc_get_product_ids_on_sale() );
	}

	$products = array();

	$wc_tax = new WC_Tax();

	$cat_names = array();

	$shop_tax = ( 'yes' === (string) get_option( 'woocommerce_calc_taxes', '' ) ) ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

	foreach ( $query->posts as $product_id ) {

		$products[] = fooeventspos_do_get_single_product( $product_id, $wc_tax, $cat_names, $shop_tax, false, $platform );

	}

	$query     = null;
	$cat_names = null;
	$wc_tax    = null;

	unset( $query, $cat_names, $wc_tax );

	$product_data['products'] = $products;

	return $product_data;
}

/**
 * Output single product.
 *
 * @since 1.0.0
 * @param int    $product_id The WooCommerce product ID.
 * @param WC_Tax $wc_tax The WooCommerce tax object.
 * @param array  $cat_names An array of category names to add to the product if it matches.
 * @param string $shop_tax Whether the shop tax is incl or excl.
 * @param bool   $force_hide_in_pos Whether the product should be forced to hide in the POS.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Single product.
 */
function fooeventspos_do_get_single_product( $product_id, &$wc_tax, &$cat_names, $shop_tax = 'incl', $force_hide_in_pos = false, $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $fooeventspos_products_default_minimum_cart_quantity;
	global $fooeventspos_products_default_cart_quantity_step;
	global $fooeventspos_products_default_cart_quantity_unit;

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	$product_data = array();

	$wc_product = wc_get_product( $product_id );

	$product_data['modified'] = (string) gmdate( 'Y-m-d H:i:s+00:00', strtotime( $wc_product->get_date_modified() ) );

	$product_data['pid']  = (string) $product_id;
	$product_data['ppdt'] = (string) strtotime( $wc_product->get_date_created() );
	$product_data['sip']  = 'no' === $wc_product->get_meta( 'fooeventspos_product_show_in_pos', true ) || $force_hide_in_pos ? '0' : '1';
	$product_data['pip']  = 'yes' === $wc_product->get_meta( 'fooeventspos_product_pin_in_pos', true ) ? '1' : '0';

	$product_title = (string) wp_strip_all_tags( html_entity_decode( $wc_product->get_title(), ENT_QUOTES, 'UTF-8' ) );

	$product_data['pt'] = $product_title;

	$current_customer = null;

	if ( WC()->customer && is_a( WC()->customer, 'WC_Customer' ) ) {
		$current_customer = WC()->customer;
	}

	WC()->customer = null;

	$product_data['ppi'] = (string) fooeventspos_get_price_including_tax( $wc_product );
	$product_data['ppe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product );

	$product_data['prpi'] = (string) fooeventspos_get_price_including_tax( $wc_product, 'regular' );
	$product_data['prpe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product, array(), 'regular' );

	$product_data['pspi'] = (string) fooeventspos_get_price_including_tax( $wc_product, 'sale' );
	$product_data['pspe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product, array(), 'sale' );
	$product_data['pph']  = (string) fooeventspos_get_price_html( $wc_product );

	$product_data['pts'] = (string) $wc_product->get_tax_status();
	$product_data['ptc'] = '' !== (string) $wc_product->get_tax_class() ? $wc_product->get_tax_class() : 'standard';

	$product_data['psm'] = 'yes' === get_option( 'woocommerce_manage_stock' ) && $wc_product->get_manage_stock() ? '1' : '0';
	$product_data['mcq'] = '1';
	$product_data['cqs'] = '1';
	$product_data['cqu'] = '';

	$use_decimal_quantities = get_option( 'globalFooEventsPOSProductsUseDecimalQuantities', 'no' ) === 'yes';

	$wc_thousand_separator = wc_get_price_thousand_separator();
	$wc_decimal_separator  = wc_get_price_decimal_separator();

	if ( $use_decimal_quantities ) {
		$fooeventspos_product_minimum_cart_quantity               = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $wc_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true ) ) );
		$fooeventspos_product_cart_quantity_step                  = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $wc_product->get_meta( 'fooeventspos_product_cart_quantity_step', true ) ) );
		$fooeventspos_product_override_default_cart_quantity_unit = $wc_product->get_meta( 'fooeventspos_product_override_default_cart_quantity_unit', true );
		$fooeventspos_product_cart_quantity_unit                  = $wc_product->get_meta( 'fooeventspos_product_cart_quantity_unit', true );

		$product_data['mcq'] = (string) ( '' !== $fooeventspos_product_minimum_cart_quantity ? $fooeventspos_product_minimum_cart_quantity : $fooeventspos_products_default_minimum_cart_quantity );
		$product_data['cqs'] = (string) ( '' !== $fooeventspos_product_cart_quantity_step ? $fooeventspos_product_cart_quantity_step : $fooeventspos_products_default_cart_quantity_step );
		$product_data['cqu'] = (string) ( 'yes' === $fooeventspos_product_override_default_cart_quantity_unit ? trim( $fooeventspos_product_cart_quantity_unit ) : $fooeventspos_products_default_cart_quantity_unit );
	}

	$product_data['ps']   = $wc_product->get_stock_quantity() !== null ? (string) str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $wc_product->get_stock_quantity() ) ) : '0';
	$product_data['pss']  = (string) $wc_product->get_stock_status();
	$product_data['pbo']  = $wc_product->backorders_allowed() ? '1' : '0';
	$product_data['psku'] = (string) trim( $wc_product->get_sku() );
	$product_data['psi']  = $wc_product->get_sold_individually() ? '1' : '0';

	$product_data['pguid'] = '';

	if ( method_exists( $wc_product, 'get_global_unique_id' ) ) {
		$product_data['pguid'] = (string) trim( $wc_product->get_global_unique_id() );
	}

	$product_variations = array();

	if ( $wc_product->is_type( 'variable' ) ) {

		$atts = $wc_product->get_variation_attributes();

		$attributes = array();

		foreach ( $atts as $att_name => $att_val ) {
			$attributes[] = $att_name;
		}

		$atts = null;

		unset( $atts );

		$variation_ids = $wc_product->get_children();

		$show_attribute_labels = get_option( 'globalFooEventsPOSProductsShowAttributeLabels', '' );

		foreach ( $variation_ids as $variation_id ) {

			$wc_product_variation = wc_get_product( $variation_id );

			if ( false === $wc_product_variation ) {
				continue;
			}

			$product_variation = array();

			$product_variation['pvid'] = (string) $variation_id;
			$product_variation['sip']  = 'no' === $wc_product_variation->get_meta( 'fooeventspos_variation_show_in_pos', true ) ? '0' : '1';
			$product_variation['pt']   = $product_title;

			$variation_attributes      = '';
			$variation_attribute_count = 0;

			foreach ( $wc_product_variation->get_attributes() as $variation_attribute_key => $variation_attribute_value ) {

				$variation_attribute_label = '';

				if ( 'yes' === $show_attribute_labels ) {
					$variation_attribute_label = ucfirst( $attributes[ $variation_attribute_count ] ) . ': ';
				}

				++$variation_attribute_count;

				$variation_attributes .= $variation_attribute_label . ucfirst( $variation_attribute_value );

				if ( $variation_attribute_count < count( $wc_product_variation->get_attributes() ) ) {
					$variation_attributes .= ', ';
				}
			}

			$product_variation['pva'] = $variation_attributes;
			$product_variation['pts'] = $product_data['pts'];
			$product_variation['ptc'] = (string) $wc_product->get_tax_class() !== '' ? $wc_product_variation->get_tax_class() : 'standard';

			$tax_rate = 0.0;

			$tax_rates = $wc_tax->get_rates_for_tax_class( $wc_product_variation->get_tax_class() );

			if ( ! empty( $tax_rates ) ) {
				$tax_rate_item = reset( $tax_rates );

				$tax_rate = (float) $tax_rate_item->tax_rate;

				$tax_rate_item = null;

				unset( $tax_rate_item );
			}

			$tax_rates = null;

			unset( $tax_rates );

			$product_variation['ptr'] = (string) $tax_rate;

			$tax_rate = null;

			unset( $tax_rate );

			$product_variation['ppi'] = (string) fooeventspos_get_price_including_tax( $wc_product_variation );
			$product_variation['ppe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product_variation );

			$product_variation['prpi'] = (string) fooeventspos_get_price_including_tax( $wc_product_variation, 'regular' );
			$product_variation['prpe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product_variation, array(), 'regular' );

			$product_variation['pspi'] = (string) fooeventspos_get_price_including_tax( $wc_product_variation, 'sale' );
			$product_variation['pspe'] = (string) fooeventspos_get_price_excluding_tax( $wc_product_variation, array(), 'sale' );
			$product_variation['pph']  = (string) fooeventspos_get_price_html( $wc_product_variation );

			$variation_thumbnail     = wp_get_attachment_image_src( $wc_product_variation->get_image_id() );
			$variation_thumbnail_url = ! empty( $variation_thumbnail ) ? $variation_thumbnail[0] : '';

			$product_variation['pi'] = '';

			$load_product_images = get_option( 'globalFooEventsPOSProductsLoadImages', 'yes' );

			if ( 'yes' === $load_product_images ) {
				$product_variation['pi'] = preg_replace_callback(
					'/[^\x20-\x7f]/',
					function ( $product_image_matches ) {
						return rawurlencode( $product_image_matches[0] );
					},
					(string) $variation_thumbnail_url
				);
			}

			$variation_manage_stock = $wc_product_variation->get_manage_stock();

			if ( 'parent' !== $variation_manage_stock ) {
				$variation_manage_stock = 'yes' === get_option( 'woocommerce_manage_stock' ) && $variation_manage_stock ? '1' : '0';
			}

			$product_variation['psm']  = $variation_manage_stock;
			$product_variation['mcq']  = $product_data['mcq'];
			$product_variation['cqs']  = $product_data['cqs'];
			$product_variation['cqu']  = $product_data['cqu'];
			$product_variation['ps']   = $wc_product_variation->get_stock_quantity() !== null ? (string) $wc_product_variation->get_stock_quantity() : '0';
			$product_variation['pss']  = (string) $wc_product_variation->get_stock_status();
			$product_variation['pbo']  = $wc_product_variation->backorders_allowed() ? '1' : '0';
			$product_variation['psku'] = (string) $wc_product_variation->get_sku();
			$product_variation['psi']  = $wc_product->get_sold_individually() ? '1' : '0';

			$product_variation['pguid'] = '';

			if ( method_exists( $wc_product_variation, 'get_global_unique_id' ) ) {
				$product_variation['pguid'] = (string) trim( $wc_product_variation->get_global_unique_id() );
			}

			FooEventsPOS_FooEvents_Integration::fooeventspos_add_variation_extra( $variation_id, $product_variation );

			$wc_product_variation = null;

			unset( $wc_product_variation );

			$product_variations[] = $product_variation;

			$product_variation = null;

			unset( $product_variation );

		}
	}

	$product_data['pv'] = $product_variations;

	$product_categories = array();

	$cat_ids = $wc_product->get_category_ids();

	$last_cat_id = end( $cat_ids );

	foreach ( $cat_ids as $cat_id ) {

		if ( empty( $cat_names[ (string) $cat_id ] ) ) {
			$cat = get_term_by( 'id', $cat_id, 'product_cat' );

			$cat_names[ (string) $cat_id ] = html_entity_decode( $cat->name, ENT_QUOTES, 'UTF-8' );

			$cat = null;

			unset( $cat );
		}

		$product_categories[] = array(
			'pcid' => (string) $cat_id,
			'pcn'  => (string) $cat_names[ (string) $cat_id ],
		);

	}

	$product_data['pc'] = $product_categories;

	$cat_ids     = null;
	$last_cat_id = null;

	unset( $cat_ids, $last_cat_id );

	// Check if FooEvents plugin is enabled.
	if ( 'Event' === $wc_product->get_meta( 'WooCommerceEventsEvent', true ) && ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) ) {

		$event = array();

		$event['et'] = $wc_product->get_meta( 'WooCommerceEventsType', true );

		$fooevents_pos_attendee_details     = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeDetails', true );
		$fooevents_pos_attendee_email       = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeEmail', true );
		$fooevents_pos_attendee_telephone   = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeTelephone', true );
		$fooevents_pos_attendee_company     = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeCompany', true );
		$fooevents_pos_attendee_designation = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeDesignation', true );

		$event['cad']   = '' === $fooevents_pos_attendee_details ? ( $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeDetails', true ) === 'on' ? '1' : '0' ) : ( 'hide' !== $fooevents_pos_attendee_details ? '1' : '0' );
		$event['cae']   = '' === $fooevents_pos_attendee_email ? ( ( $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeDetails', true ) === 'on' && $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeEmail', true ) === '' ) || $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeEmail', true ) === 'on' ? '1' : '0' ) : ( 'hide' !== $fooevents_pos_attendee_email ? '1' : '0' );
		$event['cat']   = '' === $fooevents_pos_attendee_telephone ? ( $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeTelephone', true ) === 'on' ? '1' : '0' ) : ( 'hide' !== $fooevents_pos_attendee_telephone ? '1' : '0' );
		$event['cac']   = '' === $fooevents_pos_attendee_company ? ( $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeCompany', true ) === 'on' ? '1' : '0' ) : ( 'hide' !== $fooevents_pos_attendee_company ? '1' : '0' );
		$event['cades'] = '' === $fooevents_pos_attendee_designation ? ( $wc_product->get_meta( 'WooCommerceEventsCaptureAttendeeDesignation', true ) === 'on' ? '1' : '0' ) : ( 'hide' !== $fooevents_pos_attendee_designation ? '1' : '0' );

		$event['rad']   = '' === $fooevents_pos_attendee_details ? $event['cad'] : ( 'required' === $fooevents_pos_attendee_details ? '1' : '0' );
		$event['rae']   = '' === $fooevents_pos_attendee_email ? $event['cae'] : ( 'required' === $fooevents_pos_attendee_email ? '1' : '0' );
		$event['rat']   = '' === $fooevents_pos_attendee_telephone ? $event['cat'] : ( 'required' === $fooevents_pos_attendee_telephone ? '1' : '0' );
		$event['rac']   = '' === $fooevents_pos_attendee_company ? $event['cac'] : ( 'required' === $fooevents_pos_attendee_company ? '1' : '0' );
		$event['rades'] = '' === $fooevents_pos_attendee_designation ? $event['cades'] : ( 'required' === $fooevents_pos_attendee_designation ? '1' : '0' );

		$custom_fields = array();

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {
			$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();

			$fooevents_custom_attendee_fields_options_serialized = $wc_product->get_meta( 'fooevents_custom_attendee_fields_options_serialized', true );
			$fooevents_custom_attendee_fields_options            = json_decode( $fooevents_custom_attendee_fields_options_serialized, true );
			$fooevents_custom_attendee_fields_options            = $fooevents_custom_attendee_fields->correct_legacy_options( $fooevents_custom_attendee_fields_options );

			foreach ( $fooevents_custom_attendee_fields_options as $key => $field_options ) {

				$custom_fields[] = array(
					'hash'    => $key,
					'label'   => $field_options[ $key . '_label' ],
					'type'    => $field_options[ $key . '_type' ],
					'options' => $field_options[ $key . '_options' ],
					'default' => $field_options[ $key . '_def' ],
					'req'     => 'true' === $field_options[ $key . '_req' ] ? '1' : '0',
				);

			}
		}

		$event['caf'] = $custom_fields;

		$event['eoas']   = $wc_product->get_meta( 'WooCommerceEventsAttendeeOverride', true );
		$event['eoap']   = $wc_product->get_meta( 'WooCommerceEventsAttendeeOverridePlural', true );
		$event['eots']   = $wc_product->get_meta( 'WooCommerceEventsTicketOverride', true );
		$event['eotp']   = $wc_product->get_meta( 'WooCommerceEventsTicketOverridePlural', true );
		$event['eods']   = $wc_product->get_meta( 'WooCommerceEventsDayOverride', true );
		$event['eodp']   = $wc_product->get_meta( 'WooCommerceEventsDayOverridePlural', true );
		$event['eobss']  = $wc_product->get_meta( 'WooCommerceEventsBookingsSlotOverride', true );
		$event['eobsp']  = $wc_product->get_meta( 'WooCommerceEventsBookingsSlotOverridePlural', true );
		$event['eobds']  = $wc_product->get_meta( 'WooCommerceEventsBookingsDateOverride', true );
		$event['eobdp']  = $wc_product->get_meta( 'WooCommerceEventsBookingsDateOverridePlural', true );
		$event['eobbds'] = $wc_product->get_meta( 'WooCommerceEventsBookingsBookingDetailsOverride', true );
		$event['eobbdp'] = $wc_product->get_meta( 'WooCommerceEventsBookingsBookingDetailsOverridePlural', true );
		$event['eosr']   = $wc_product->get_meta( 'WooCommerceEventsSeatingRowOverride', true );
		$event['eosrp']  = $wc_product->get_meta( 'WooCommerceEventsSeatingRowOverridePlural', true );
		$event['eoss']   = $wc_product->get_meta( 'WooCommerceEventsSeatingSeatOverride', true );
		$event['eossp']  = $wc_product->get_meta( 'WooCommerceEventsSeatingSeatOverridePlural', true );
		$event['eossc']  = $wc_product->get_meta( 'WooCommerceEventsSeatingSeatingChartOverride', true );
		$event['eosscp'] = $wc_product->get_meta( 'WooCommerceEventsSeatingSeatingChartOverridePlural', true );
		$event['eosf']   = $wc_product->get_meta( 'WooCommerceEventsSeatingFrontOverride', true );
		$event['eosfp']  = $wc_product->get_meta( 'WooCommerceEventsSeatingFrontOverridePlural', true );

		$event['eue'] = 'on' === $wc_product->get_meta( 'WooCommerceEventsUniqueEmail', true ) ? '1' : '0';

		$event['info'] = array();

		if ( function_exists( 'get_fooevents_event_info' ) ) {
			$event_info_orig = get_fooevents_event_info( get_post( $product_id ) );

			$exclude_array = array(
				'ProductID',
				'Name',
				'TicketLogo',
				'TicketHeaderImage',
				'AttendeeOverride',
				'AttendeeOverridePlural',
				'DayOverride',
				'DayOverridePlural',
				'BookingsBookingDetailsOverride',
				'BookingsBookingDetailsOverridePlural',
				'BookingsSlotOverride',
				'BookingsSlotOverridePlural',
				'BookingsDateOverride',
				'BookingsDateOverridePlural',
				'SeatingRowOverride',
				'SeatingRowOverridePlural',
				'SeatingSeatOverride',
				'SeatingSeatOverridePlural',
				'SeatingSeatingChartOverride',
				'SeatingSeatingChartOverridePlural',
				'SeatingFrontOverride',
				'SeatingFrontOverridePlural',
				'BookingOptionIDs',
				'BookingOptions',
				'Type',
			);

			foreach ( $event_info_orig as $key => $val ) {

				$new_key = str_replace( 'WooCommerceEvents', '', $key );

				if ( in_array( $new_key, $exclude_array, true ) ) {
					continue;
				}

				$event['info'][ $new_key ] = $val;
			}
		}

		FooEventsPOS_FooEvents_Integration::fooeventspos_add_event_extra( $product_id, $event );

		$product_data['fee'] = $event;

		$custom_fields = null;
		$event         = null;

		unset( $custom_fields, $event );
	}

	$product_data['pao'] = array();

	if ( ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) || is_plugin_active_for_network( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) && class_exists( 'WC_Product_Addons_Helper' ) ) {
		$product_data['pao'] = WC_Product_Addons_Helper::get_product_addons( $product_id );

		foreach ( $product_data['pao'] as &$product_addon ) {
			foreach ( $product_addon as $key => &$value ) {
				if ( 'options' !== $key ) {
					$value = (string) $value;
				}
			}
		}
	}

	WC()->customer = $current_customer;

	return $product_data;
}

/**
 * Fetch all orders.
 *
 * @since 1.0.0
 * @param int    $offset The offset from where to start adding fetched orders.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Orders.
 */
function fooeventspos_do_get_all_orders( $offset = 0, $platform = 'any' ) {

	$order_data = array();

	$only_load_pos_orders = 'yes' === get_option( 'globalFooEventsPOSOnlyLoadPOSOrders', '' );

	$order_load_statuses = get_option( 'globalFooEventsPOSOrderLoadStatuses', array( 'completed', 'cancelled', 'refunded' ) );

	if ( empty( $order_load_statuses ) ) {
		$order_load_statuses = array( 'completed', 'cancelled', 'refunded' );
	}

	array_walk(
		$order_load_statuses,
		function ( &$value ) {
			$value = 'wc-' . $value;
		}
	);

	$args = array(
		'limit'   => -1,
		'type'    => 'shop_order',
		'status'  => $order_load_statuses,
		'return'  => 'ids',
		'orderby' => 'ID',
	);

	if ( $only_load_pos_orders ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array(
				'key'   => '_fooeventspos_order_source',
				'value' => 'fooeventspos_app',
			),
			array(
				'key'   => 'Order Source',
				'value' => 'FooEvents POS app',
			),
		);
	}

	$order_ids = wc_get_orders( $args );

	$args = null;

	unset( $args );

	$total_orders = count( $order_ids );

	$orders_to_load = (int) get_option( 'globalFooEventsPOSOrdersToLoad', '100' );

	if ( ! empty( $orders_to_load ) && $orders_to_load > 0 && $total_orders > $orders_to_load ) {
		$total_orders = $orders_to_load;
	}

	$order_data['total_orders'] = $total_orders . '_total_orders';
	$order_data['orders']       = array();

	$max_orders   = 200;
	$orders_start = ( $offset * $max_orders ) + 1;
	$orders_end   = ( $offset * $max_orders ) + $max_orders;

	if ( ! empty( $orders_to_load ) && $orders_to_load > 0 ) {
		if ( $orders_end > $orders_to_load ) {
			$orders_end = $orders_to_load;
		}
	}

	$order_count = 0;

	foreach ( $order_ids as $order_id ) {

		++$order_count;

		if ( $order_count < $orders_start ) {
			continue;
		}

		$wc_order = wc_get_order( $order_id );

		$order_data['orders'][] = fooeventspos_do_get_single_order( $wc_order, $platform );

		$wc_order = null;

		unset( $wc_order );

		if ( $order_count === $orders_end ) {
			break;
		}
	}

	return $order_data;
}

/**
 * Fetch all customers.
 *
 * @since 1.0.0
 * @param int    $offset The offset from where to start adding fetched customers.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Customers.
 */
function fooeventspos_do_get_all_customers( $offset = 0, $platform = 'any' ) {

	$customer_data = array();
	$max_users     = 1000;

	$args = array(
		'role__in'      => get_option( 'globalFooEventsPOSCustomerUserRole', array( 'customer', 'subscriber' ) ),
		'number'        => $max_users,
		'offset'        => $offset * $max_users,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'orderby'       => array( 'user_email', 'user_login' ),
		'order'         => 'ASC',
	);

	$query = new WP_User_Query( $args );

	$args['number'] = -1;
	$args['offset'] = 0;

	$count_query = new WP_User_Query( $args );

	$args = null;

	unset( $args );

	$customer_data['total_customers'] = $count_query->total_users . '_total_customers';

	$count_query = null;

	unset( $count_query );

	$customer_data['customers'] = array();

	$customer_ids = $query->get_results();

	foreach ( $customer_ids as $customer_id ) {

		$customer_data['customers'][] = fooeventspos_get_single_customer( $customer_id, $platform );

	}

	$query = null;

	unset( $query );

	return $customer_data;
}

/**
 * Output single customer.
 *
 * @since 1.0.0
 * @param int    $id The WordPress post ID of a customer.
 * @param string $platform The platform that is currently performing this request.
 */
function fooeventspos_get_single_customer( $id, $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$customer_data = array();

	$customer = get_userdata( $id );

	$customer_data['registered'] = (string) gmdate( 'Y-m-d H:i:s+00:00', strtotime( $customer->user_registered ) );

	$customer_data['cid'] = (string) $id;
	$customer_data['cfn'] = trim( ! empty( $customer->first_name ) && null !== $customer->first_name ? $customer->first_name : '' );
	$customer_data['cln'] = trim( ! empty( $customer->last_name ) && null !== $customer->last_name ? $customer->last_name : '' );
	$customer_data['cun'] = trim( ! empty( $customer->user_login ) && null !== $customer->user_login ? $customer->user_login : '' );
	$customer_data['ce']  = trim( ! empty( $customer->user_email ) && null !== $customer->user_email ? $customer->user_email : '' );

	$customer = null;

	unset( $customer );

	$customer_fields = array(
		'cbfn' => 'billing_first_name',
		'cbln' => 'billing_last_name',
		'cbco' => 'billing_company',
		'cba1' => 'billing_address_1',
		'cba2' => 'billing_address_2',
		'cbc'  => 'billing_city',
		'cbpo' => 'billing_postcode',
		'cbcu' => 'billing_country',
		'cbs'  => 'billing_state',
		'cbph' => 'billing_phone',
		'cbe'  => 'billing_email',
		'csfn' => 'shipping_first_name',
		'csln' => 'shipping_last_name',
		'csco' => 'shipping_company',
		'csa1' => 'shipping_address_1',
		'csa2' => 'shipping_address_2',
		'csc'  => 'shipping_city',
		'cspo' => 'shipping_postcode',
		'cscu' => 'shipping_country',
		'css'  => 'shipping_state',
		'csph' => 'shipping_phone',
	);

	$customer_meta = get_user_meta( $id );

	foreach ( $customer_fields as $customer_key => $meta_key ) {
		$val = '';

		if ( ! empty( $customer_meta[ $meta_key ] ) ) {
			$val = $customer_meta[ $meta_key ][0];
		}

		$customer_data[ $customer_key ] = trim( null !== $val ? $val : '' );
	}

	return $customer_data;
}

/**
 * Fetch chunked data.
 *
 * @since 1.0.0
 * @param WP_User $user The WordPress user object.
 * @param string  $chunk The specific chunk of data to fetch.
 * @param string  $platform The platform that is currently performing this request.
 *
 * @return array Chunked data.
 */
function fooeventspos_fetch_chunk( $user, $chunk, $platform = 'any' ) {

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	$data = array();

	if ( 'store_settings' === $chunk ) {

		$data['user'] = json_decode( wp_json_encode( $user->data ), true );

		foreach ( $data['user'] as $user_key => &$user_value ) {
			$user_value = (string) $user_value;
		}

		$data['plugin_version'] = apply_filters( 'fooeventspos_current_plugin_version', '' );

		$data['admin_url_post']              = admin_url( 'post.php?action=edit&post=' );
		$data['admin_url_user']              = admin_url( 'user-edit.php?user_id=' );
		$data['admin_url_fooeventspos_settings'] = admin_url( 'admin.php?page=fooeventspos-settings' );

		$temp_config = null;

		unset( $temp_config );

		// Get app settings.
		$data['check_stock_availability'] = get_option( 'globalFooEventsPOSCheckStockAvailability', '' ) === 'yes' ? '1' : '0';

		$data['use_decimal_quantities'] = get_option( 'globalFooEventsPOSProductsUseDecimalQuantities', 'no' ) === 'yes' ? '1' : '0';
		$data['order_limit']            = get_option( 'globalFooEventsPOSOrdersToLoad', '100' );

		// Order load statuses.
		$order_load_statuses          = array();
		$order_load_status_values     = fooeventspos_get_all_order_statuses( 'load' );
		$order_selected_load_statuses = get_option( 'globalFooEventsPOSOrderLoadStatuses', array( 'completed', 'cancelled', 'refunded' ) );

		if ( empty( $order_selected_load_statuses ) ) {
			$order_selected_load_statuses = array( 'completed', 'cancelled', 'refunded' );
		}

		foreach ( $order_selected_load_statuses as $order_status ) {
			$order_load_statuses[ $order_status ] = $order_load_status_values[ $order_status ];
		}

		// Order submit statuses.
		$order_submit_statuses          = array();
		$order_submit_status_values     = fooeventspos_get_all_order_statuses( 'submit' );
		$order_selected_submit_statuses = get_option( 'globalFooEventsPOSOrderSubmitStatuses', array( 'completed' ) );

		if ( empty( $order_selected_submit_statuses ) ) {
			$order_selected_submit_statuses = array( 'completed' );
		}

		foreach ( $order_selected_submit_statuses as $order_submit_status ) {
			$order_submit_statuses[ $order_submit_status ] = $order_submit_status_values[ $order_submit_status ];
		}

		$default_order_status = get_option( 'globalFooEventsPOSDefaultOrderStatus', 'completed' );

		if ( ! in_array( $default_order_status, $order_submit_statuses, true ) ) {
			$order_submit_statuses[ $default_order_status ] = $order_submit_status_values[ $default_order_status ];
		}

		// Order incomplete statuses.
		$order_incomplete_statuses          = array();
		$order_incomplete_status_values     = fooeventspos_get_all_order_statuses( 'incomplete' );
		$order_selected_incomplete_statuses = get_option( 'globalFooEventsPOSOrderIncompleteStatuses', array( '' ) );

		if ( empty( $order_selected_incomplete_statuses ) ) {
			$order_selected_incomplete_statuses = array( '' );
		}

		foreach ( $order_selected_incomplete_statuses as $order_incomplete_status ) {
			$order_incomplete_statuses[ $order_incomplete_status ] = '' !== $order_incomplete_status ? $order_incomplete_status_values[ $order_incomplete_status ] : '';
		}

		// New order alert statuses.
		$new_order_alert_selected_statuses = get_option( 'globalFooEventsPOSNewOrderAlertStatuses', array() );

		if ( '' === $new_order_alert_selected_statuses ) {
			$new_order_alert_selected_statuses = array();
		}

		// New order alert shipping methods.
		$new_order_alert_selected_shipping_methods = get_option( 'globalFooEventsPOSNewOrderAlertShippingMethods', array() );

		if ( '' === $new_order_alert_selected_shipping_methods || null === $new_order_alert_selected_shipping_methods ) {
			$new_order_alert_selected_shipping_methods = array();
		}

		$data['order_statuses']                   = $order_load_statuses;
		$data['order_submit_statuses']            = $order_submit_statuses;
		$data['default_order_status']             = get_option( 'globalFooEventsPOSDefaultOrderStatus', 'completed' );
		$data['order_incomplete_statuses']        = $order_incomplete_statuses;
		$data['order_shipping_methods']           = fooeventspos_get_available_shipping_methods();
		$data['new_order_alert_statuses']         = $new_order_alert_selected_statuses;
		$data['new_order_alert_shipping_methods'] = $new_order_alert_selected_shipping_methods;
		$data['default_order_customer']           = get_option( 'globalFooEventsPOSDefaultCustomer', '' );
		$data['store_logo_url']                   = trim( get_option( 'globalFooEventsPOSStoreLogoURL', '' ) );
		$data['store_name']                       = trim( get_option( 'globalFooEventsPOSStoreName', '' ) );
		$data['receipt_header']                   = trim( str_replace( "\r\n", '<br />', get_option( 'globalFooEventsPOSHeaderContent', '' ) ) );
		$data['receipt_title']                    = trim( get_option( 'globalFooEventsPOSReceiptTitle', '' ) );
		$data['receipt_order_number_prefix']      = trim( get_option( 'globalFooEventsPOSOrderNumberPrefix', '' ) );
		$data['receipt_product_column_title']     = trim( get_option( 'globalFooEventsPOSProductColumnTitle', '' ) );
		$data['receipt_quantity_column_title']    = trim( get_option( 'globalFooEventsPOSQuantityColumnTitle', '' ) );
		$data['receipt_price_column_title']       = trim( get_option( 'globalFooEventsPOSPriceColumnTitle', '' ) );
		$data['receipt_subtotal_column_title']    = trim( get_option( 'globalFooEventsPOSSubtotalColumnTitle', '' ) );
		$data['receipt_show_sku']                 = get_option( 'globalFooEventsPOSShowSKU', 'yes' ) === 'yes' ? '1' : '0';
		$data['receipt_show_guid']                = get_option( 'globalFooEventsPOSShowGUID', 'yes' ) === 'yes' ? '1' : '0';
		$data['receipt_inclusive_abbreviation']   = trim( get_option( 'globalFooEventsPOSInclusiveAbbreviation', '' ) );
		$data['receipt_exclusive_abbreviation']   = trim( get_option( 'globalFooEventsPOSExclusiveAbbreviation', '' ) );
		$data['receipt_discounts_title']          = trim( get_option( 'globalFooEventsPOSDiscountsTitle', '' ) );
		$data['receipt_refunds_title']            = trim( get_option( 'globalFooEventsPOSRefundsTitle', '' ) );
		$data['receipt_tax_title']                = trim( get_option( 'globalFooEventsPOSTaxTitle', '' ) );
		$data['receipt_total_title']              = trim( get_option( 'globalFooEventsPOSTotalTitle', '' ) );
		$data['receipt_payment_method']           = trim( get_option( 'globalFooEventsPOSPaymentMethodTitle', '' ) );
		$data['receipt_show_billing_address']     = get_option( 'globalFooEventsPOSShowBillingAddress', 'yes' ) === 'yes' ? '1' : '0';
		$data['receipt_billing_address_title']    = trim( get_option( 'globalFooEventsPOSBillingAddressTitle', '' ) );
		$data['receipt_show_shipping_address']    = get_option( 'globalFooEventsPOSShowShippingAddress', 'yes' ) === 'yes' ? '1' : '0';
		$data['receipt_shipping_address_title']   = trim( get_option( 'globalFooEventsPOSShippingAddressTitle', '' ) );
		$data['receipt_footer']                   = trim( str_replace( "\r\n", '<br />', get_option( 'globalFooEventsPOSFooterContent', '' ) ) );
		$data['receipt_show_logo']                = get_option( 'globalFooEventsPOSReceiptShowLogo', 'yes' ) === 'yes' ? '1' : '0';

		$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID', '' );

		$data['square_application_id'] = $square_application_id;

		$square_locations = array();

		if ( '' !== $square_application_id ) {

			$square_locations_result = fooeventspos_get_square_locations();

			if ( 'success' === $square_locations_result['status'] ) {

				foreach ( $square_locations_result['locations'] as $square_location ) {

					$square_locations[] = array(
						'id'       => $square_location['id'],
						'name'     => $square_location['name'],
						'status'   => $square_location['status'],
						'currency' => $square_location['currency'],
					);

				}
			}
		}

		$data['square_locations'] = $square_locations;

		$stripe_publishable_key = get_option( 'globalFooEventsPOSStripePublishableKey', '' );
		$stripe_secret_key      = get_option( 'globalFooEventsPOSStripeSecretKey', '' );

		$data['stripe_configured']      = '' !== $stripe_secret_key ? '1' : '0';
		$data['stripe_publishable_key'] = $stripe_publishable_key;

		$stripe_locations = array();

		if ( '' !== $stripe_secret_key ) {

			$stripe_locations_result = fooeventspos_get_stripe_locations();
			$stripe_readers_result   = fooeventspos_get_stripe_readers();

			if ( 'success' === $stripe_locations_result['status'] ) {

				foreach ( $stripe_locations_result['locations'] as $stripe_location ) {

					$temp_location = array(
						'id'           => $stripe_location['id'],
						'display_name' => $stripe_location['display_name'],
						'readers'      => array(),
					);

					if ( 'success' === $stripe_readers_result['status'] ) {

						foreach ( $stripe_readers_result['readers'] as $stripe_reader ) {

							if ( $stripe_reader['location'] === $stripe_location['id'] ) {
								$temp_location['readers'][] = $stripe_reader;
							}
						}
					}

					$stripe_locations[ $stripe_location['id'] ] = $temp_location;

					unset( $temp_location );
				}
			}
		}

		$data['stripe_locations'] = $stripe_locations;

		// Check if FooEvents plugin is enabled.
		$data['fooevents_active'] = is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ? '1' : '0';

		$settings = array(
			'c'   => get_woocommerce_currency(),
			'cs'  => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
			'ct'  => 'yes' === (string) get_option( 'woocommerce_calc_taxes', '' ) ? '1' : '0',
			'pit' => 'yes' === (string) get_option( 'woocommerce_prices_include_tax', '' ) ? '1' : '0',
			'rsl' => 'yes' === (string) get_option( 'woocommerce_tax_round_at_subtotal', '' ) ? '1' : '0',
			'ec'  => 'yes' === (string) get_option( 'woocommerce_enable_coupons', '' ) ? '1' : '0',
		);

		$settings['cpt'] = '1' === $settings['ct'] ? (string) get_option( 'woocommerce_tax_display_cart', '' ) : 'incl';
		$settings['spt'] = '1' === $settings['ct'] ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

		$default_tax_location        = (string) get_option( 'woocommerce_default_country', '' );
		$default_tax_location_values = explode( ':', $default_tax_location );

		if ( 1 === count( $default_tax_location_values ) ) {
			$default_tax_location .= ':';
		}

		$settings['dtl'] = '1' === $settings['ct'] ? $default_tax_location . ':' . get_option( 'woocommerce_store_postcode' ) : '';

		$settings['ttd'] = '1' === $settings['ct'] ? (string) get_option( 'woocommerce_tax_total_display', '' ) : 'single';
		$settings['tbo'] = '1' === $settings['ct'] ? (string) get_option( 'woocommerce_tax_based_on', '' ) : 'base';

		$currency_format = html_entity_decode( get_woocommerce_price_format(), ENT_QUOTES, 'UTF-8' );
		$currency_format = str_replace( '%1$s', $settings['cs'], $currency_format );
		$currency_format = str_replace( '%2$s', '%@', $currency_format );

		$settings['cf'] = $currency_format;
		$settings['ts'] = wc_get_price_thousand_separator();
		$settings['ds'] = wc_get_price_decimal_separator();

		if ( '' === $settings['ds'] ) {
			$settings['ds'] = '.';
		}

		if ( $settings['ts'] === $settings['ds'] ) {
			$settings['ts'] = ' ';
		}

		$settings['nd'] = (string) wc_get_price_decimals();

		$date_format = get_option( 'date_format' );

		$settings['dfjs']      = fooeventspos_convert_php_date_format( $date_format, 'js' );
		$settings['dfios']     = fooeventspos_convert_php_date_format( $date_format, 'ios' );
		$settings['dfandroid'] = fooeventspos_convert_php_date_format( $date_format, 'android' );

		$time_format = get_option( 'time_format' );

		$settings['tfjs']      = fooeventspos_convert_php_time_format( $time_format, 'js' );
		$settings['tfios']     = fooeventspos_convert_php_time_format( $time_format, 'ios' );
		$settings['tfandroid'] = fooeventspos_convert_php_time_format( $time_format, 'android' );

		$data['settings'] = $settings;

		$data['categories'] = fooeventspos_do_get_all_product_categories();

		$data['payment_methods'] = fooeventspos_do_get_all_payment_methods();

		$data['tax_rates'] = fooeventspos_do_get_all_tax_rates();

		if ( 'fe-web' === $platform ) {
			if ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) {
				$data['fooevents_settings'] = fooevents_append_output_data( array() )['data'];
			}

			FooEvents_POS_Integration::fooeventspos_add_appearance_values( $data );
		}
	} elseif ( strpos( $chunk, 'customers' ) !== false ) {

		$data = fooeventspos_do_get_all_customers( (int) substr( $chunk, strlen( 'customers' ) ), $platform );

	} elseif ( strpos( $chunk, 'orders' ) !== false ) {

		$data = fooeventspos_do_get_all_orders( (int) substr( $chunk, strlen( 'orders' ) ), $platform );

	} elseif ( strpos( $chunk, 'products' ) !== false ) {

		$data = fooeventspos_do_get_all_products( (int) substr( $chunk, strlen( 'products' ) ), $platform );

	} elseif ( strpos( $chunk, 'product_images' ) !== false ) {

		$data = fooeventspos_do_get_all_product_images( (int) substr( $chunk, strlen( 'product_images' ) ), $platform );

	}

	return $data;
}

/**
 * Update product.
 *
 * @since 1.0.0
 * @param array  $product_params Key/value pairs of product data to update.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Update product result.
 */
function fooeventspos_do_update_product( $product_params, $platform = 'any' ) {

	$product_id = $product_params['pid'];

	try {
		$wc_product = wc_get_product( $product_id );

		if ( null === $wc_product || false === $wc_product ) {
			return array();
		}

		if ( isset( $product_params['pt'] ) ) {
			$wc_product->set_name( $product_params['pt'] );
		}

		if ( isset( $product_params['pp'] ) ) {
			$wc_product->set_price( $product_params['pp'] );
		}

		if ( isset( $product_params['prp'] ) ) {
			$wc_product->set_regular_price( $product_params['prp'] );
		}

		if ( isset( $product_params['psp'] ) ) {
			$wc_product->set_sale_price( $product_params['psp'] );
		}

		if ( isset( $product_params['psku'] ) ) {
			$wc_product->set_sku( $product_params['psku'] );
		}

		if ( isset( $product_params['pguid'] ) ) {
			if ( method_exists( $wc_product, 'set_global_unique_id' ) ) {
				$wc_product->set_global_unique_id( $product_params['pguid'] );
			}
		}

		if ( isset( $product_params['psm'] ) ) {
			$manage_stock = $product_params['psm'];

			if ( 'parent' !== $manage_stock ) {
				$manage_stock = '1' === $product_params['psm'];
			}

			$wc_product->set_manage_stock( $manage_stock );
		}

		if ( isset( $product_params['ps'] ) ) {
			wc_update_product_stock( $wc_product, $product_params['ps'] );
		}

		if ( isset( $product_params['pss'] ) ) {
			$wc_product->set_stock_status( $product_params['pss'] );
		}

		if ( isset( $product_params['psi'] ) ) {
			$wc_product->set_sold_individually( '1' === $product_params['psi'] );
		}

		ob_start();
		$wc_product->save();
		ob_end_clean();
	} catch ( Exception $e ) {
		return array();
	}

	$wc_tax = new WC_Tax();

	$cat_names = array();

	$shop_tax = ( (string) get_option( 'woocommerce_calc_taxes', '' ) === 'yes' ) ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

	if ( 'variation' === $wc_product->get_type() ) {
		$product_id = $wc_product->get_parent_id();
	}

	$updated_product  = fooeventspos_do_get_single_product( $product_id, $wc_tax, $cat_names, $shop_tax, false, $platform );
	$sale_product_ids = implode( ',', wc_get_product_ids_on_sale() );

	return array(
		'updated_product'  => $updated_product,
		'sale_product_ids' => $sale_product_ids,
	);
}

/**
 * Set WC()->customer from $order_customer.
 *
 * @since 1.10.0
 * @param array $order_customer The order customer data.
 */
function fooeventspos_set_wc_customer( $order_customer ) {
	if ( '' !== $order_customer['cid'] && (int) $order_customer['cid'] > 0 ) {
		WC()->customer = new WC_Customer( (int) $order_customer['cid'], true );

		if ( trim( $order_customer['cbfn'] ) !== '' ) {
			WC()->customer->set_billing_first_name( $order_customer['cbfn'] );
		} else {
			WC()->customer->set_billing_first_name( $order_customer['cfn'] );
		}

		if ( trim( $order_customer['cbln'] ) !== '' ) {
			WC()->customer->set_billing_last_name( $order_customer['cbln'] );
		} else {
			WC()->customer->set_billing_last_name( $order_customer['cln'] );
		}

		WC()->customer->set_billing_company( $order_customer['cbco'] );
		WC()->customer->set_billing_address_1( $order_customer['cba1'] );
		WC()->customer->set_billing_address_2( $order_customer['cba2'] );
		WC()->customer->set_billing_city( $order_customer['cbc'] );
		WC()->customer->set_billing_postcode( $order_customer['cbpo'] );
		WC()->customer->set_billing_country( $order_customer['cbcu'] );
		WC()->customer->set_billing_state( $order_customer['cbs'] );
		WC()->customer->set_billing_phone( $order_customer['cbph'] );
		WC()->customer->set_billing_email( $order_customer['cbe'] );

		WC()->customer->set_shipping_first_name( $order_customer['csfn'] );
		WC()->customer->set_shipping_last_name( $order_customer['csln'] );
		WC()->customer->set_shipping_company( $order_customer['csco'] );
		WC()->customer->set_shipping_address_1( $order_customer['csa1'] );
		WC()->customer->set_shipping_address_2( $order_customer['csa2'] );
		WC()->customer->set_shipping_city( $order_customer['csc'] );
		WC()->customer->set_shipping_postcode( $order_customer['cspo'] );
		WC()->customer->set_shipping_country( $order_customer['cscu'] );
		WC()->customer->set_shipping_state( $order_customer['css'] );
		WC()->customer->set_shipping_phone( $order_customer['csph'] );
	}
}

/**
 * Create a new WooCommerce order or update and existing order.
 *
 * @since 1.0.0
 * @param array  $order_data Key/value pairs containing order data needed to create a new WooCommerce order.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return WC_Order Created or updated order.
 */
function fooeventspos_do_create_update_order( $order_data, $platform = 'any' ) {

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

	// Check if FooEvents plugin is enabled.
	$is_fooevents_enabled = false;

	if ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) {
		$is_fooevents_enabled = true;
	}

	WC()->frontend_includes();
	WC()->session = new WC_Session_Handler();
	WC()->session->init();
	WC()->customer = new WC_Customer( 0, true );
	WC()->cart     = new WC_Cart();
	WC()->cart->empty_cart();

	$order_date                  = $order_data[0];
	$payment_method_key          = $order_data[1];
	$coupons                     = json_decode( stripslashes( $order_data[2] ), true );
	$order_items                 = json_decode( stripslashes( $order_data[3] ), true );
	$order_customer              = json_decode( $order_data[4], true );
	$order_note                  = urldecode( $order_data[5] );
	$order_note_send_to_customer = (int) $order_data[6];
	$attendee_details            = json_decode( stripslashes( $order_data[7] ), true );
	$square_order_id             = $order_data[8];
	$user_id                     = $order_data[9];
	$stripe_payment_id           = $order_data[10];
	$analytics                   = json_decode( stripslashes( $order_data[11] ), true );
	$order_status                = $order_data[12];
	$existing_order_id           = $order_data[13];
	$payments                    = json_decode( $order_data[14], true );

	// Check for null in json_decode.
	$coupons          = null !== $coupons ? $coupons : array();
	$order_items      = null !== $order_items ? $order_items : array();
	$order_customer   = null !== $order_customer ? $order_customer : array();
	$attendee_details = null !== $attendee_details ? $attendee_details : array();
	$analytics        = null !== $analytics ? $analytics : array();
	$payments         = null !== $payments ? $payments : array();

	$wc_order = ! empty( $existing_order_id ) ? wc_get_order( $existing_order_id ) : wc_create_order();

	$updated_values = array();

	$wc_order->update_meta_data( '_fooeventspos_order_source', 'fooeventspos_app' );

	// Order date.
	if ( ! empty( $order_date ) ) {
		$wc_order->set_date_created( (int) $order_date );

		$updated_values[] = $fooeventspos_phrases['text_order_update_date'];
	}

	// Payment method.
	if ( ! empty( $payment_method_key ) ) {
		$changed_fooeventspos_payment_method = $wc_order->update_meta_data( '_fooeventspos_payment_method', $payment_method_key );

		$wc_order->set_payment_method( fooeventspos_get_wc_payment_method_from_fooeventspos_key( $payment_method_key ) );

		$payment_method = fooeventspos_get_payment_method_from_key( $payment_method_key );

		$wc_order->set_payment_method_title( $payment_method );

		$wc_order->update_meta_data( $fooeventspos_phrases['meta_key_order_payment_method'], $payment_method );

		if ( false !== $changed_fooeventspos_payment_method ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_payment_method'];
		}
	}

	// Square payment.
	if ( ! empty( $square_order_id ) && in_array(
		$payment_method_key,
		array(
			'fooeventspos_square',
			'fooeventspos_square_manual',
			'fooeventspos_square_terminal',
			'fooeventspos_square_reader',
		),
		true
	) ) {

		$changed_fooeventspos_square_order_id = $wc_order->update_meta_data( '_fooeventspos_square_order_id', $square_order_id );

		$square_order_result = fooeventspos_get_square_order( $square_order_id );

		if ( 'success' === $square_order_result['status'] ) {

			$square_order = $square_order_result['order'];

			if ( ! empty( $square_order['tenders'] ) && count( $square_order['tenders'] ) === 1 ) {

				$changed_fooeventspos_square_order_id = $wc_order->update_meta_data( '_fooeventspos_square_order_auto_refund', '1' );

			}
		}

		if ( false !== $changed_fooeventspos_square_order_id ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_square_order_id'];
		}
	}

	// Stripe payment.
	if ( ! empty( $stripe_payment_id ) && in_array(
		$payment_method_key,
		array(
			'fooeventspos_stripe_manual',
			'fooeventspos_stripe_reader',
			'fooeventspos_stripe_chipper',
			'fooeventspos_stripe_wisepad',
			'fooeventspos_stripe_reader_m2',
		),
		true
	) ) {
		$changed_fooeventspos_stripe_payment_id = $wc_order->update_meta_data( '_fooeventspos_stripe_payment_id', $stripe_payment_id );

		if ( false !== $changed_fooeventspos_stripe_payment_id ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_stripe_payment_id'];

			fooeventspos_add_wc_order_number_to_stripe_payment( $stripe_payment_id, $wc_order->get_order_number() );
		}
	}

	// User ID.
	if ( ! empty( $user_id ) && (int) $user_id > 0 ) {
		$changed_fooeventspos_user_id = $wc_order->update_meta_data( '_fooeventspos_user_id', $user_id );

		if ( false !== $changed_fooeventspos_user_id ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_cashier'];
		}
	}

	$order_has_event = false;

	foreach ( $order_items as $order_item ) {
		$wc_product = wc_get_product( $order_item['pid'] );

		if ( $is_fooevents_enabled ) {
			$event_product_id = $order_item['pid'];

			if ( $wc_product->get_type() === 'variation' ) {
				$event_product_id = $wc_product->get_parent_id();
			}

			$event_product = wc_get_product( $event_product_id );

			if ( 'Event' === $event_product->get_meta( 'WooCommerceEventsEvent', true ) ) {
				$order_has_event = true;

				break;
			}
		}
	}

	// Order customer.
	if ( 0 === (int) $order_customer['cid'] && ! empty( $order_customer['ce'] ) ) {
		// First create new customer.
		$create_result = fooeventspos_do_create_update_customer( $order_customer, $platform );

		if ( 'success' === $create_result['status'] ) {

			$order_customer['cid'] = (string) $create_result['cid'];

		} else {
			// Customer possibly already exists.
			$existing_customer = get_user_by( 'email', $order_customer['ce'] );

			if ( is_object( $existing_customer ) && is_a( $existing_customer, 'WP_User' ) ) {
				$order_customer['cid'] = (string) $existing_customer->ID;
			}
		}
	}

	if ( ( '' !== $order_customer['cid'] && (int) $order_customer['cid'] > 0 ) || ( ! empty( $existing_order_id ) && ! empty( $order_customer ) ) ) {
		if ( $is_fooevents_enabled && $order_has_event ) {
			fooeventspos_set_wc_customer( $order_customer );
		}

		$changed_fooeventspos_customer_id = (int) $wc_order->get_customer_id() !== (int) $order_customer['cid'];

		$wc_order->set_customer_id( (int) $order_customer['cid'] );

		if ( trim( $order_customer['cbfn'] ) !== '' ) {
			$wc_order->set_billing_first_name( $order_customer['cbfn'] );
		} else {
			$wc_order->set_billing_first_name( $order_customer['cfn'] );
		}

		if ( trim( $order_customer['cbln'] ) !== '' ) {
			$wc_order->set_billing_last_name( $order_customer['cbln'] );
		} else {
			$wc_order->set_billing_last_name( $order_customer['cln'] );
		}

		$wc_order->set_billing_company( $order_customer['cbco'] );
		$wc_order->set_billing_address_1( $order_customer['cba1'] );
		$wc_order->set_billing_address_2( $order_customer['cba2'] );
		$wc_order->set_billing_city( $order_customer['cbc'] );
		$wc_order->set_billing_postcode( $order_customer['cbpo'] );
		$wc_order->set_billing_country( $order_customer['cbcu'] );
		$wc_order->set_billing_state( $order_customer['cbs'] );
		$wc_order->set_billing_phone( $order_customer['cbph'] );
		$wc_order->set_billing_email( $order_customer['cbe'] );

		$wc_order->set_shipping_first_name( $order_customer['csfn'] );
		$wc_order->set_shipping_last_name( $order_customer['csln'] );
		$wc_order->set_shipping_company( $order_customer['csco'] );
		$wc_order->set_shipping_address_1( $order_customer['csa1'] );
		$wc_order->set_shipping_address_2( $order_customer['csa2'] );
		$wc_order->set_shipping_city( $order_customer['csc'] );
		$wc_order->set_shipping_postcode( $order_customer['cspo'] );
		$wc_order->set_shipping_country( $order_customer['cscu'] );
		$wc_order->set_shipping_state( $order_customer['css'] );
		$wc_order->set_shipping_phone( $order_customer['csph'] );

		if ( false !== $changed_fooeventspos_customer_id ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_customer'];
		}
	}

	$analytics_event_info  = array();
	$analytics_total_items = 0;

	$updated_product_ids = array();
	$product_updates     = array();

	$wc_tax = new WC_Tax();

	$cat_names = array();

	$shop_tax = ( (string) get_option( 'woocommerce_calc_taxes', '' ) === 'yes' ) ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

	// Order items.
	if ( ! empty( $existing_order_id ) && ! empty( $order_items ) ) {
		// First remove all items before adding new items.
		$wc_order_items = $wc_order->get_items();

		foreach ( $wc_order_items as $wc_order_item ) {
			$product_variation_id = $wc_order_item['product_id'];

			if ( $wc_order_item['variation_id'] > 0 ) {
				$product_variation_id = $wc_order_item['variation_id'];
			}

			$wc_product_variation = wc_get_product( $product_variation_id );

			if ( false !== $wc_product_variation && $wc_product_variation->get_manage_stock() ) {
				wc_update_product_stock( $wc_product_variation, $wc_order_item->get_quantity(), 'increase' );
			}

			$wc_product_variation = null;

			unset( $wc_product_variation );

			$wc_order->remove_item( $wc_order_item->get_id() );

			if ( false === in_array( $wc_order_item['product_id'], $updated_product_ids, true ) ) {
				$updated_product_ids[] = $wc_order_item['product_id'];

				$product_updates[] = fooeventspos_do_get_single_product( $wc_order_item['product_id'], $wc_tax, $cat_names, $shop_tax, false, $platform );
			}
		}

		FooEventsPOS_FooEvents_Integration::fooeventspos_restock_booking_slots( $existing_order_id );

		$updated_values[] = $fooeventspos_phrases['text_order_update_items'];
	}

	$wc_order->{'product_updates'} = wp_json_encode( $product_updates );

	foreach ( $order_items as $order_item ) {
		$wc_product = wc_get_product( $order_item['pid'] );

		$line_total_excl = $order_item['oilst'];

		$product_args = array(
			'total' => $line_total_excl,
		);

		if ( ! empty( $coupons ) ) {
			$product_args['subtotal'] = $line_total_excl;
		}

		$is_event = false;

		if ( $is_fooevents_enabled ) {
			$event_product_id = $order_item['pid'];

			if ( $wc_product->get_type() === 'variation' ) {
				$event_product_id = $wc_product->get_parent_id();
			}

			$event_product = wc_get_product( $event_product_id );
			$is_event      = 'Event' === $event_product->get_meta( 'WooCommerceEventsEvent', true );

			if ( $is_event ) {
				$order_has_event = true;

				$event_type = $event_product->get_meta( 'WooCommerceEventsType', true );

				if ( ! isset( $analytics_event_info[ $event_type . '_event_tickets' ] ) ) {
					$analytics_event_info[ $event_type . '_event_tickets' ] = 0;
				}

				if ( ! isset( $analytics_event_info[ $event_type . '_event_total' ] ) ) {
					$analytics_event_info[ $event_type . '_event_total' ] = 0.0;
				}

				$analytics_event_info[ $event_type . '_event_tickets' ] += (int) $order_item['oiq'];
				$analytics_event_info[ $event_type . '_event_total' ]   += (float) $line_total_excl;
			}

			$variation_id = 0;
			$attributes   = array();

			if ( 'variation' === $wc_product->get_type() ) {
				$variation_id = $order_item['pid'];
				$attributes   = $wc_product->get_attributes();
			}

			if ( $order_has_event ) {
				WC()->cart->add_to_cart( $order_item['pid'], $order_item['oiq'], $variation_id, $attributes );
			}
		}

		$order_item_quantity = $order_item['oiq'];

		if ( $is_event && $order_item_quantity > 1 ) {
			for ( $i = 0; $i < $order_item_quantity; $i++ ) {
				$product_args = array(
					'total' => $line_total_excl / $order_item_quantity,
				);

				if ( ! empty( $coupons ) ) {
					$product_args['subtotal'] = $line_total_excl / $order_item_quantity;
				}

				$wc_order->add_product( $wc_product, 1, $product_args );
			}
		} else {
			$wc_order->add_product( $wc_product, $order_item['oiq'], $product_args );
		}

		if ( ! empty( $existing_order_id ) ) {
			if ( false !== $wc_product && $wc_product->get_manage_stock() ) {
				wc_update_product_stock( $wc_product, $order_item['oiq'], 'decrease' );
			}
		}

		$analytics_total_items += (int) $order_item['oiq'];

		$product_args = null;

		unset( $product_args );
	}

	$wc_order->calculate_totals();

	// Coupon codes.
	if ( ! empty( $coupons ) ) {
		foreach ( $coupons as $coupon ) {
			$wc_order->apply_coupon( new WC_Coupon( $coupon ) );
		}
	}

	// Order note.
	if ( '' !== $order_note ) {
		$wc_order->add_order_note( $order_note, $order_note_send_to_customer );
	}

	if ( $is_fooevents_enabled && $order_has_event ) {

		ob_start();
		$wc_order->save();
		ob_end_clean();

		if ( ! empty( $attendee_details ) ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_attendee_details'];

			foreach ( $attendee_details as $key => $val ) {
				$_POST[ $key ] = sanitize_text_field( $val );
			}
		}

		$fooevents_config = new FooEvents_Config();

		// Require CheckoutHelper.
		require_once $fooevents_config->class_path . 'class-fooevents-checkout-helper.php';
		$fooevents_checkout_helper = new FooEvents_Checkout_Helper( $fooevents_config );

		$fooevents_checkout_helper->woocommerce_events_process( $wc_order->get_id() );
	}

	WC()->session->destroy_session();
	WC()->cart->empty_cart();

	// Order status.
	if ( ! empty( $order_status ) ) {
		if ( 'wc-' !== substr( $order_status, 0, 3 ) ) {
			$order_status = 'wc-' . $order_status;
		}

		$wc_order->set_status( $order_status );

		if ( ! empty( $existing_order_id ) ) {
			$updated_values[] = $fooeventspos_phrases['text_order_update_status'];
		}
	} elseif ( empty( $existing_order_id ) ) {
		$wc_order->set_status( 'completed' );
	}

	// Payments.
	$temp_order_payments = array();

	if ( null !== $payments && ! empty( $payments ) ) {
		if ( ! empty( $existing_order_id ) ) {
			// First remove old order payments.
			$order_payments = new WP_Query(
				array(
					'post_type'      => array( 'fooeventspos_payment' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'   => '_order_id',
							'value' => $existing_order_id,
						),
					),
				)
			);

			if ( $order_payments->have_posts() ) {
				foreach ( $order_payments->posts as $order_payment_id ) {
					wp_delete_post( $order_payment_id, true );
				}
			}
		}

		foreach ( $payments as &$payment ) {
			$payment['oid'] = (string) $wc_order->get_id();

			if ( ! empty( $payment['pd'] ) ) {
				$payment['pd'] = (string) $payment['pd'];
			} else {
				$payment['pd'] = (string) $wc_order->get_date_created()->getTimestamp();
			}

			$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment );

			if ( ! is_wp_error( $payment_post ) ) {
				$payment['fspid'] = (string) $payment_post['ID'];
				$payment['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
				$payment['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
				$payment['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );
			}
		}

		$temp_order_payments = $payments;

		$wc_order->update_meta_data( '_fooeventspos_payments', wp_json_encode( $temp_order_payments ) );
	} elseif ( empty( $existing_order_id ) ) {
		$payment_args = array(
			'pd'   => $wc_order->get_date_created()->getTimestamp(),
			'oid'  => $wc_order->get_id(),
			'opmk' => $payment_method_key,
			'oud'  => $user_id,
			'tid'  => '' !== $square_order_id ? $square_order_id : ( '' !== $stripe_payment_id ? $stripe_payment_id : '' ),
			'pa'   => $wc_order->get_total(),
			'np'   => '1',
			'pn'   => '1',
			'pap'  => '1',
			'par'  => 'refunded' === $wc_order->get_status() ? '1' : '0',
		);

		$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment_args );

		if ( ! is_wp_error( $payment_post ) ) {
			$payment_args['fspid'] = (string) $payment_post['ID'];
			$payment_args['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
			$payment_args['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
			$payment_args['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

			$temp_order_payments = array( $payment_args );

			$wc_order->update_meta_data( '_fooeventspos_payments', wp_json_encode( $temp_order_payments ) );
		}
	}

	if ( ! empty( $temp_order_payments ) ) {
		$square_payment_methods = array(
			'fooeventspos_square',
			'fooeventspos_square_manual',
			'fooeventspos_square_terminal',
			'fooeventspos_square_reader',
		);

		$order_payment_methods = array_column( $temp_order_payments, 'opmk' );
		$has_square_payment    = (bool) array_intersect( $square_payment_methods, $order_payment_methods );

		if ( $has_square_payment ) {
			wp_schedule_single_event( time() + 15, 'fooeventspos_update_order_square_fees', array( (string) $wc_order->get_id() ) );
		}
	}

	if ( ! empty( $existing_order_id ) ) {
		fooeventspos_update_modified_date( $existing_order_id );

		if ( ! empty( $user_id ) ) {
			$fooeventspos_user = get_userdata( $user_id );

			if ( false !== $fooeventspos_user ) {
				$fooeventspos_user_display_name = $fooeventspos_user->display_name;

				$wc_order->add_order_note( sprintf( $fooeventspos_phrases['text_order_updated_via_fooeventspos'], $fooeventspos_user_display_name, "\n- " . implode( "\n- ", $updated_values ) ) );
			}
		}
	}

	ob_start();
	$wc_order->save();
	ob_end_clean();

	FooEventsPOS_FooEvents_Integration::fooeventspos_auto_checkin_order_tickets( $wc_order->get_id(), isset( $attendee_details['autoCheckin'] ) && 'yes' === $attendee_details['autoCheckin'] );

	// Anonymous analytics data.
	if ( 'yes' === get_option( 'globalFooEventsPOSAnalyticsOptIn' ) && empty( $existing_order_id ) && ! empty( $order_items ) ) {

		$analytics['passphrase']     = '438ce09ef2fe06a2f56051a3a6919100';
		$analytics['grouping_key']   = get_option( 'globalFooEventsPOSSalt' );
		$analytics['line_items']     = count( $order_items );
		$analytics['total_items']    = $analytics_total_items;
		$analytics['payment_method'] = $payment_method_key;
		$analytics['total']          = $wc_order->get_total();
		$analytics['lang_wp']        = str_replace( '_', '-', get_locale() );

		$woocommerce_onboarding_profile = get_option( 'woocommerce_onboarding_profile', array() );

		$wc_industries = array();

		if ( ! empty( $woocommerce_onboarding_profile['industry'] ) ) {
			foreach ( $woocommerce_onboarding_profile['industry'] as $industry ) {
				$wc_industries[] = is_array( $industry ) ? ( isset( $industry['detail'] ) && ! empty( $industry['detail'] ) ? $industry['detail'] : ( isset( $industry['slug'] ) && ! empty( $industry['slug'] ) ? $industry['slug'] : '' ) ) : $industry;
			}
		}

		$analytics['sector'] = implode( ', ', $wc_industries );

		$analytics = array_merge( $analytics, $analytics_event_info );

		$analytics['auto_checkin'] = isset( $attendee_details['autoCheckin'] ) ? ( 'yes' === $attendee_details['autoCheckin'] ? '1' : '0' ) : '';

		$response = wp_remote_post(
			'https://analytics.fooevents.com',
			array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'headers'     => array(
					'Content-type' => 'application/x-www-form-urlencoded',
				),
				'body'        => $analytics,
			)
		);

	}

	return $wc_order;
}

/**
 * Output a single order.
 *
 * @since 1.0.0
 * @param WC_Order $wc_order The WooCommerce order object to get.
 * @param string   $platform The platform that is currently performing this request.
 * @param bool     $get_product_updates Get the latest information for products in this order.
 *
 * @return array Single order.
 */
function fooeventspos_do_get_single_order( &$wc_order, $platform = 'any', $get_product_updates = false ) {

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

	$single_order = array();

	$single_order['modified'] = (string) gmdate( 'Y-m-d H:i:s+00:00', strtotime( $wc_order->get_date_modified() ) );

	$single_order['product_updates'] = array();

	if ( isset( $wc_order->product_updates ) ) {
		$single_order['product_updates'] = json_decode( $wc_order->product_updates );
	}

	$wc_order = wc_get_order( $wc_order->get_id() );

	$single_order['oid'] = (string) $wc_order->get_id();
	$single_order['on']  = (string) $wc_order->get_order_number();
	$single_order['od']  = (string) strtotime( $wc_order->get_date_created() );

	$order_date_modified = (string) strtotime( $wc_order->get_date_modified() );

	$single_order['odm'] = '' !== $order_date_modified ? $order_date_modified : $single_order['od'];
	$single_order['os']  = (string) $wc_order->get_status();
	$single_order['ost'] = (string) $wc_order->get_subtotal();
	$single_order['odt'] = (string) $wc_order->get_discount_total();
	$single_order['ot']  = (string) $wc_order->get_total();
	$single_order['ott'] = (string) $wc_order->get_total_tax();

	$tax_totals = $wc_order->get_tax_totals();
	$tax_lines  = array();

	foreach ( $tax_totals as $tax_key => $tax_total ) {

		$tax_lines[] = array(
			'tax_total' => (string) $tax_total->amount,
			'label'     => (string) $tax_total->label,
			'rate_id'   => (string) $tax_total->rate_id,
		);
	}

	$single_order['otl'] = $tax_lines;

	$tax_lines  = null;
	$tax_totals = null;

	unset( $tax_lines, $tax_totals );

	$payment_method_key = (string) $wc_order->get_meta( '_fooeventspos_payment_method', true );

	if ( '' === $payment_method_key ) {

		$payment_method = (string) $wc_order->get_meta( $fooeventspos_phrases['meta_key_order_payment_method'], true );

		if ( '' === $payment_method ) {
			$payment_method = (string) $wc_order->get_meta( 'Order Payment Method', true );
		}

		$payment_method_key = fooeventspos_get_payment_method_key_from_value( $payment_method );

		$payment_method = null;

		unset( $payment_method );

		$wc_order->update_meta_data( '_fooeventspos_payment_method', $payment_method_key );
		$wc_order->save();

	} elseif ( 'fooeventspos_square' === $payment_method_key ) {

		$payment_method_key = 'fooeventspos_square_reader';

		$wc_order->update_meta_data( '_fooeventspos_payment_method', $payment_method_key );
		$wc_order->save();

	}

	$single_order['opmk'] = $payment_method_key;
	$single_order['sfa']  = (string) $wc_order->get_meta( '_fooeventspos_square_fee_amount', true );

	$fooeventspos_payments_json = $wc_order->get_meta( '_fooeventspos_payments', true );

	if ( '' === $fooeventspos_payments_json ) {
		$order_payments = new WP_Query(
			array(
				'post_type'      => array( 'fooeventspos_payment' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_order_id',
						'value' => $wc_order->get_id(),
					),
				),
			)
		);

		if ( $order_payments->have_posts() ) {
			// Regenerate order payments meta from existing payment posts.
			$order_payments_array = array();

			foreach ( $order_payments->posts as $order_payment_id ) {
				$order_payments_array[] = array(
					'pd'    => (string) strtotime( get_post_datetime( $order_payment_id, 'date', 'gmt' )->format( 'Y-m-d H:i:s+00:00' ) ),
					'oid'   => (string) $wc_order->get_id(),
					'opmk'  => get_post_meta( $order_payment_id, '_payment_method_key', true ),
					'oud'   => get_post_meta( $order_payment_id, '_cashier', true ),
					'tid'   => get_post_meta( $order_payment_id, '_transaction_id', true ),
					'pa'    => get_post_meta( $order_payment_id, '_amount', true ),
					'np'    => get_post_meta( $order_payment_id, '_number_of_payments', true ),
					'pn'    => get_post_meta( $order_payment_id, '_payment_number', true ),
					'pap'   => get_post_meta( $order_payment_id, '_payment_paid', true ),
					'par'   => get_post_meta( $order_payment_id, '_payment_refunded', true ),
					'pe'    => get_post_meta( $order_payment_id, '_payment_extra', true ),
					'soar'  => get_post_meta( $order_payment_id, '_fooeventspos_square_order_auto_refund', true ),
					'sfa'   => get_post_meta( $order_payment_id, '_fooeventspos_square_fee_amount', true ),
					'fspid' => (string) $order_payment_id,
				);
			}

			$fooeventspos_payments_json = wp_json_encode( $order_payments_array );
		} else {
			// Create new payment post for order.
			$payment_args = array(
				'pd'   => $wc_order->get_date_created()->getTimestamp(),
				'oid'  => $wc_order->get_id(),
				'opmk' => $wc_order->get_meta( '_fooeventspos_payment_method', true ),
				'oud'  => $wc_order->get_meta( '_fooeventspos_user_id', true ),
				'tid'  => '' !== $wc_order->get_meta( '_fooeventspos_square_order_id', true ) ? $wc_order->get_meta( '_fooeventspos_square_order_id', true ) : ( '' !== $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) ? $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) : '' ),
				'pa'   => $wc_order->get_total(),
				'np'   => '1',
				'pn'   => '1',
				'pap'  => '1',
				'par'  => 'refunded' === $wc_order->get_status() ? '1' : '0',
			);

			$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment_args );

			if ( ! is_wp_error( $payment_post ) ) {
				$payment_args['fspid'] = (string) $payment_post['ID'];
				$payment_args['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
				$payment_args['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
				$payment_args['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

				$fooeventspos_payments_json = wp_json_encode( array( $payment_args ) );
			}
		}

		$wc_order->update_meta_data( '_fooeventspos_payments', $fooeventspos_payments_json );

		ob_start();
		$wc_order->save();
		ob_end_clean();
	}

	$single_order['op'] = '' !== $fooeventspos_payments_json ? json_decode( $fooeventspos_payments_json, true ) : array();

	$order_source = (string) $wc_order->get_meta( '_fooeventspos_order_source', true );

	$order_all_notes        = array();
	$customer_notes_content = array();

	$customer_provided_note = $wc_order->get_customer_note();

	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );

	if ( false === strpos( $time_format, ':s' ) && false !== strpos( $time_format, ':i' ) ) {
		$time_format = str_replace( ':i', ':i:s', $time_format );
	}

	if ( '' !== $customer_provided_note ) {
		$wc_order_date_created = $wc_order->get_date_created();

		$order_all_notes[] = array(
			'id'            => (string) $wc_order->get_id(),
			'date'          => (string) gmdate( $date_format . ' ' . $time_format, $wc_order_date_created->getOffsetTimestamp() ),
			'author'        => $fooeventspos_phrases['text_customer_provided_note'],
			'content'       => wp_strip_all_tags( $customer_provided_note ),
			'customer_note' => '1',
		);

		$customer_notes_content[] = $customer_provided_note;
	}

	$order_comments_args = array(
		'post_id' => $wc_order->get_id(),
		'approve' => 'approve',
		'type'    => '',
		'order'   => 'DESC',
	);

	remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

	$order_comments = get_comments( $order_comments_args );

	foreach ( $order_comments as $order_comment ) {
		$is_customer_note = false;

		if ( get_comment_meta( $order_comment->comment_ID, 'is_customer_note', true ) ) {
			$customer_notes_content[] = $order_comment->comment_content;

			$is_customer_note = true;
		} elseif ( 'yes' !== get_option( 'globalFooEventsPOSFetchOrderNotes', '' ) ) {
			continue;
		}

		$order_all_notes[] = array(
			'id'            => $order_comment->comment_ID,
			'date'          => (string) gmdate( $date_format . ' ' . $time_format, strtotime( $order_comment->comment_date ) ),
			'author'        => $order_comment->comment_author,
			'content'       => html_entity_decode( wp_strip_all_tags( str_replace( array( "\r", "\n" ), ' ', $order_comment->comment_content ) ), ENT_QUOTES, 'UTF-8' ),
			'customer_note' => true === $is_customer_note ? '1' : '0',
		);
	}

	add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

	$single_order['oan'] = $order_all_notes;
	$single_order['ocn'] = implode( '<br />', $customer_notes_content );

	$single_order['fo'] = 'fooeventspos_app' === $order_source ? '1' : '0';

	$single_order['soid'] = (string) $wc_order->get_meta( '_fooeventspos_square_order_id', true );
	$single_order['spid'] = (string) $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true );
	$single_order['oud']  = (string) $wc_order->get_meta( '_fooeventspos_user_id', true );

	$payment_method_key = null;

	unset( $payment_method_key );

	$single_order['ort'] = (string) ( '' === $wc_order->get_total_refunded() ? '0' : $wc_order->get_total_refunded() );

	$order_refunds = $wc_order->get_refunds();

	$order_refund_items = array();

	foreach ( $order_refunds as $order_refund ) {
		$refund_items = $order_refund->get_items();

		foreach ( $refund_items as $refund_item ) {
			$order_item_id = '';

			$meta_data = $refund_item->get_meta_data();

			foreach ( $meta_data as $meta_data_item ) {
				if ( '_refunded_item_id' === $meta_data_item->key ) {
					$order_item_id = (string) $meta_data_item->value;

					break;
				}
			}

			if ( empty( $order_refund_items[ $order_item_id ] ) ) {
				$order_refund_items[ $order_item_id ] = array(
					'qty'   => 0,
					'total' => 0,
				);
			}

			$order_refund_items[ $order_item_id ]['qty']   += abs( $refund_item->get_quantity() );
			$order_refund_items[ $order_item_id ]['total'] += abs( $refund_item['total'] ) + abs( $refund_item['total_tax'] );
		}
	}

	$order_refunds = null;

	$customer_data = array(
		'cid'  => '',
		'cfn'  => '',
		'cln'  => '',
		'cun'  => '',
		'ce'   => '',
		'cbfn' => $wc_order->get_billing_first_name(),
		'cbln' => $wc_order->get_billing_last_name(),
		'cbco' => $wc_order->get_billing_company(),
		'cba1' => $wc_order->get_billing_address_1(),
		'cba2' => $wc_order->get_billing_address_2(),
		'cbc'  => $wc_order->get_billing_city(),
		'cbpo' => $wc_order->get_billing_postcode(),
		'cbcu' => $wc_order->get_billing_country(),
		'cbs'  => $wc_order->get_billing_state(),
		'cbph' => $wc_order->get_billing_phone(),
		'cbe'  => $wc_order->get_billing_email(),
		'csfn' => $wc_order->get_shipping_first_name(),
		'csln' => $wc_order->get_shipping_last_name(),
		'csco' => $wc_order->get_shipping_company(),
		'csa1' => $wc_order->get_shipping_address_1(),
		'csa2' => $wc_order->get_shipping_address_2(),
		'csc'  => $wc_order->get_shipping_city(),
		'cspo' => $wc_order->get_shipping_postcode(),
		'cscu' => $wc_order->get_shipping_country(),
		'css'  => $wc_order->get_shipping_state(),
		'csph' => $wc_order->get_shipping_phone(),
	);

	if ( $wc_order->get_customer_id() > 0 ) {

		$customer_data['cid'] = (string) $wc_order->get_customer_id();

		$customer = get_userdata( $wc_order->get_customer_id() );

		$customer_data['cfn'] = ! empty( $customer->first_name ) && null !== $customer->first_name ? $customer->first_name : '';
		$customer_data['cln'] = ! empty( $customer->last_name ) && null !== $customer->last_name ? $customer->last_name : '';
		$customer_data['cun'] = ! empty( $customer->user_login ) && null !== $customer->user_login ? $customer->user_login : '';
		$customer_data['ce']  = ! empty( $customer->user_email ) && null !== $customer->user_email ? $customer->user_email : '';

	}

	$single_order['oc'] = $customer_data;

	$customer_data = null;

	unset( $customer_data );

	$single_order['osm'] = $wc_order->get_shipping_method();

	$order_shipping_methods = array();

	foreach ( $wc_order->get_shipping_methods() as $order_shipping_method ) {
		$order_shipping_methods[] = $order_shipping_method->get_method_id();
	}

	$single_order['osmid'] = $order_shipping_methods;

	$updated_product_ids = array();

	$wc_tax = new WC_Tax();

	$cat_names = array();

	$shop_tax = ( (string) get_option( 'woocommerce_calc_taxes', '' ) === 'yes' ) ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

	$order_ticket_ids                 = array();
	$woocommerce_events_order_tickets = array();

	if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) ) {

		$single_order['etg'] = '' !== $wc_order->get_meta( 'WooCommerceEventsTicketsGenerated', true ) ? '1' : '0';

		$woocommerce_events_sent_tickets = $wc_order->get_meta( 'WooCommerceEventsTicketsGenerated', true );

		$order_selected_incomplete_statuses = get_option( 'globalFooEventsPOSOrderIncompleteStatuses', array( '' ) );

		if ( empty( $order_selected_incomplete_statuses ) ) {
			$order_selected_incomplete_statuses = array( '' );
		}

		if ( ( in_array( $single_order['os'], $order_selected_incomplete_statuses, true ) && '1' === $single_order['etg'] ) || '' !== $woocommerce_events_sent_tickets ) {
			$order_tickets = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'key'   => 'WooCommerceEventsOrderID',
							'value' => $wc_order->get_id(),
						),
					),
				)
			);

			$order_ticket_ids = $order_tickets->posts;
		}

		if ( in_array( $single_order['os'], $order_selected_incomplete_statuses, true ) && class_exists( 'FooEvents_Config' ) && class_exists( 'FooEvents_Orders_Helper' ) ) {
			$fooevents_orders_helper = new FooEvents_Orders_Helper( new FooEvents_Config() );

			if ( '1' === $single_order['etg'] ) {
				$event_tickets = array();

				foreach ( $order_ticket_ids as $order_ticket_id ) {

					$event_tickets[] = (object) array( 'ID' => $order_ticket_id );

				}

				$woocommerce_events_order_tickets = $fooevents_orders_helper->process_event_tickets_for_display( $event_tickets );
			} else {
				$woocommerce_events_order_tickets = $wc_order->get_meta( 'WooCommerceEventsOrderTickets', true );
				$woocommerce_events_order_tickets = '' !== $woocommerce_events_order_tickets ? $fooevents_orders_helper->process_order_tickets_for_display( $woocommerce_events_order_tickets ) : array();
			}
		}

		FooEventsPOS_FooEvents_Integration::fooeventspos_add_order_ticket_data( $single_order, $order_ticket_ids, $platform );
		FooEventsPOS_FooEvents_Integration::fooeventspos_add_order_ticket_stationary_data( $single_order, $order_ticket_ids, $platform );

	}

	$single_order['oet'] = $woocommerce_events_order_tickets;

	$single_order['oetd'] = array();

	if ( ! empty( $order_ticket_ids ) ) {

		$hide_personal_info = get_option( 'globalWooCommerceEventsAppHidePersonalInfo', false );

		foreach ( $order_ticket_ids as $order_ticket_id ) {
			$order_ticket_post_meta = get_post_meta( $order_ticket_id );

			$order_ticket_post_meta_keys = array_keys( $order_ticket_post_meta );

			array_walk(
				$order_ticket_post_meta,
				function ( &$value, $key ) {
					$value = $value[0];

					$personal_details_keys = array(
						'WooCommerceEventsPurchaserEmail',
						'WooCommerceEventsPurchaserPhone',
						'WooCommerceEventsAttendeeEmail',
						'WooCommerceEventsAttendeeTelephone',
						'WooCommerceEventsAttendeeCompany',
						'WooCommerceEventsAttendeeDesignation',
					);

					if ( $hide_personal_info && in_array( $key, $personal_details_keys, true ) ) {
						$value = '***';
					}
				}
			);

			$event_type = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsType', true );

			$order_ticket_post_meta['WooCommerceEventsType'] = $event_type;

			$order_ticket_post_meta['WooCommerceEventsAttendeeOverride']       = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsAttendeeOverride', true );
			$order_ticket_post_meta['WooCommerceEventsAttendeeOverridePlural'] = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsAttendeeOverridePlural', true );

			if ( 'bookings' === $event_type ) {
				$bookings_slot_term = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsSlotOverride', true );
				$bookings_date_term = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsDateOverride', true );

				if ( empty( $bookings_slot_term ) ) {

					$slot_label = __( 'Slot', 'fooevents-bookings' );

				} else {

					$slot_label = $bookings_slot_term;

				}

				if ( empty( $bookings_date_term ) ) {

					$date_label = __( 'Date', 'fooevents-bookings' );

				} else {

					$date_label = $bookings_date_term;

				}

				$order_ticket_post_meta['WooCommerceEventsBookingsSlotOverride'] = $slot_label;
				$order_ticket_post_meta['WooCommerceEventsBookingsDateOverride'] = $date_label;
			} elseif ( 'seating' === $event_type ) {
				$row_keys = array_filter(
					$order_ticket_post_meta_keys,
					function ( $order_ticket_post_meta_key ) {
						return 0 === strpos( $order_ticket_post_meta_key, 'fooevents_seat_row_name_' );
					}
				);

				$row_key = reset( $row_keys );

				$order_ticket_post_meta['WooCommerceEventsSeatingRow'] = $order_ticket_post_meta[ $row_key ];

				$row_text = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsSeatingRowOverride', true );

				if ( '' === $row_text ) {
					$row_text = __( 'Row', 'fooevents-seating' );
				}

				$seat_keys = array_filter(
					$order_ticket_post_meta_keys,
					function ( $order_ticket_post_meta_key ) {
						return false !== strpos( $order_ticket_post_meta_key, 'fooevents_seat_number_' );
					}
				);

				$seat_key = reset( $seat_keys );

				$order_ticket_post_meta['WooCommerceEventsSeatingSeat'] = $order_ticket_post_meta[ $seat_key ];

				$seat_text = get_post_meta( $order_ticket_post_meta['WooCommerceEventsProductID'], 'WooCommerceEventsSeatingSeatOverride', true );

				if ( '' === $seat_text ) {
					$seat_text = __( 'Seat', 'fooevents-seating' );
				}

				$order_ticket_post_meta['WooCommerceEventsSeatingRowOverride']  = $row_text;
				$order_ticket_post_meta['WooCommerceEventsSeatingSeatOverride'] = $seat_text;
			}

			$single_order['oetd'][] = $order_ticket_post_meta;
		}
	}

	$order_items = array();

	$wc_order_items = $wc_order->get_items();

	$event_count = 0;

	foreach ( $wc_order_items as $wc_order_item ) {

		$order_item = array();

		$product_id = $wc_order_item['product_id'];

		if ( (int) $wc_order_item['variation_id'] > 0 ) {
			$product_id = (int) $wc_order_item['variation_id'];
		}

		$order_item['oiid']   = (string) $wc_order_item->get_id();
		$order_item['oin']    = (string) $wc_order_item->get_name();
		$order_item['oipid']  = (string) $product_id;
		$order_item['oivid']  = (string) $wc_order_item['variation_id'];
		$order_item['oivpid'] = (string) $wc_order_item['product_id'];
		$order_item['oilst']  = (string) $wc_order_item['line_subtotal'];
		$order_item['oilstt'] = (string) $wc_order_item['line_subtotal_tax'];
		$order_item['oiltx']  = (string) $wc_order_item['total_tax'];
		$order_item['oiltl']  = (string) $wc_order_item['total'];
		$order_item['oiq']    = (string) $wc_order_item['qty'];

		$order_item_taxes = array();

		if ( ! empty( $wc_order_item['taxes']['total'] ) ) {
			foreach ( $wc_order_item['taxes']['total'] as $tax_rate_id => $tax_rate_total ) {
				if ( '' !== $tax_rate_id && '' !== $tax_rate_total ) {
					$order_item_taxes[ $tax_rate_id ] = $tax_rate_total;
				}
			}
		}

		$order_item['oit'] = $order_item_taxes;

		$order_item['oits'] = ''; // Unused value to be removed after next app updates.
		$order_item['oitc'] = '' !== $wc_order_item['tax_class'] ? $wc_order_item['tax_class'] : 'standard';

		$refunded_quantity = '0';
		$refunded_total    = '0';

		if ( ! empty( $order_refund_items[ (string) $wc_order_item->get_id() ] ) ) {
			$refunded_quantity = (string) $order_refund_items[ (string) $wc_order_item->get_id() ]['qty'];
			$refunded_total    = (string) $order_refund_items[ (string) $wc_order_item->get_id() ]['total'];
		}

		$order_item['oirq'] = (string) $refunded_quantity;
		$order_item['oirt'] = (string) $refunded_total;

		$order_item['oipao'] = array();

		if ( is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) || is_plugin_active_for_network( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {
			$order_item['oipao'] = $wc_order_item->get_meta( '_pao_ids' );

			if ( '' === $order_item['oipao'] ) {
				$order_item['oipao'] = array();
			}

			foreach ( $order_item['oipao'] as &$product_addon ) {
				foreach ( $product_addon as $key => &$value ) {
					$value = (string) $value;
				}
			}
		}

		$order_item['oietd'] = array();

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) ) {
			if ( 'Event' === get_post_meta( $wc_order_item['product_id'], 'WooCommerceEventsEvent', true ) ) {
				if ( ! empty( $single_order['oetd'] ) && count( $single_order['oetd'] ) > $event_count ) {
					for ( $ticket_count = 0; $ticket_count < (int) $order_item['oiq']; $ticket_count++ ) {
						if ( isset( $single_order['oetd'] ) && isset( $single_order['oetd'][ $event_count ] ) ) {
							$order_item['oietd'][] = $single_order['oetd'][ $event_count ];
						}

						++$event_count;
					}
				}
			}
		}

		if ( true === $get_product_updates ) {
			if ( false === in_array( $wc_order_item['product_id'], $updated_product_ids, true ) ) {
				$updated_product_ids[] = $wc_order_item['product_id'];

				$single_order['product_updates'][] = fooeventspos_do_get_single_product( $wc_order_item['product_id'], $wc_tax, $cat_names, $shop_tax, false, $platform );
			}
		}

		$product_id = null;

		unset( $product_id );

		$order_items[] = $order_item;

		$order_item = null;

		unset( $order_item );
	}

	$single_order['oi'] = $order_items;

	$wc_order_items     = null;
	$order_refund_items = null;

	unset( $wc_order_items, $order_refunds, $order_refund_items );

	$coupon_lines = array();

	$coupons = $wc_order->get_items( 'coupon' );

	if ( ! empty( $coupons ) ) {

		foreach ( $coupons as $coupon ) {

			$coupon_lines[] = array(
				'oclc'  => $coupon->get_code(),
				'ocld'  => $coupon->get_discount(),
				'ocldt' => $coupon->get_discount_tax(),
			);

		}
	}

	$single_order['ocl'] = $coupon_lines;

	return $single_order;
}

/**
 * Synchronize offline data.
 *
 * @since 1.0.0
 * @param array  $offline_changes An array containing key/value pairs of other arrays containing offline changes.
 * @param string $platform The platform that is currently performing this request.
 */
function fooeventspos_do_sync_offline_changes( $offline_changes = array(), $platform = 'any' ) {
	$result = array(
		'status' => 'success',
	);

	$created_updated_order_ids = array();
	$created_updated_orders    = array();
	$cancelled_order_ids       = array();

	$last_offline_change = end( $offline_changes );

	foreach ( $offline_changes as $offline_change ) {
		if ( ! empty( $offline_change['update_product'] ) ) {

			$update_product_params = $offline_change['update_product']['FooEventsPOSProductParams'];

			$result = fooeventspos_do_update_product( json_decode( $update_product_params, true ), $platform );

			$response = array();

			if ( ! empty( $result ) ) {
				$response['ocid'] = $offline_change['update_product']['ocid'];
			}

			echo wp_json_encode( $response );
		} elseif ( ! empty( $offline_change['create_update_order'] ) || ! empty( $offline_change['create_order'] ) ) {
			$order_params = ! empty( $offline_change['create_update_order'] ) ? $offline_change['create_update_order'] : $offline_change['create_order'];
			$temp_id      = $order_params['temp_id'];

			$created_updated_order = fooeventspos_do_create_update_order(
				array(
					$order_params['date'],
					$order_params['payment_method_key'],
					wp_json_encode( ! empty( $order_params['coupons'] ) ? $order_params['coupons'] : array() ),
					wp_json_encode( ! empty( $order_params['items'] ) ? $order_params['items'] : array() ),
					wp_json_encode( ! empty( $order_params['customer'] ) ? $order_params['customer'] : array() ),
					$order_params['order_note'],
					$order_params['order_note_send_to_customer'],
					wp_json_encode( ! empty( $order_params['attendee_details'] ) ? $order_params['attendee_details'] : array() ),
					$order_params['square_order_id'],
					$order_params['user_id'],
					$order_params['stripe_payment_id'],
					wp_json_encode( ! empty( $order_params['analytics'] ) ? $order_params['analytics'] : array() ),
					$order_params['status'],
					$order_params['existing_order_id'],
					$order_params['payments'],
				),
				$platform
			);

			$created_updated_order_ids[] = array(
				'temp_id' => (string) $temp_id,
				'oid'     => (string) $created_updated_order->get_id(),
				'on'      => (string) $created_updated_order->get_order_number(),
			);

			$order_items = $created_updated_order->get_items();

			$created_updated_orders[ (string) $temp_id ] = array();

			$response = array();

			$response[ (string) $temp_id ] = array();

			foreach ( $order_items as $order_item ) {
				$created_updated_orders[ (string) $temp_id ][] = array(
					'oiid'  => (string) $order_item->get_id(),
					'oipid' => (string) $order_item['product_id'],
				);

				$response[ (string) $temp_id ][] = array(
					'oiid'  => (string) $order_item->get_id(),
					'oipid' => (string) $order_item['product_id'],
				);
			}

			$response['ocid'] = ! empty( $offline_change['create_update_order'] ) ? $offline_change['create_update_order']['ocid'] : $offline_change['create_order']['ocid'];

			$response['newOrderID'] = array(
				'temp_id' => (string) $temp_id,
				'oid'     => (string) $created_updated_order->get_id(),
				'on'      => (string) $created_updated_order->get_order_number(),
			);

			echo wp_json_encode( $response );
		} elseif ( ! empty( $offline_change['cancel_order'] ) ) {
			$cancel_order_params = $offline_change['cancel_order'];
			$temp_id             = '';
			$cancel_id           = $cancel_order_params['oid'];

			if ( strpos( $cancel_id, '_' ) !== false ) {
				$temp_id = $cancel_id;

				foreach ( $created_updated_order_ids as $created_updated_order_id ) {
					if ( $created_updated_order_id['temp_id'] === $cancel_id ) {
						$cancel_id = $created_updated_order_id['oid'];
					}
				}
			}

			fooeventspos_do_cancel_order( $cancel_id, (bool) $cancel_order_params['restock'], $platform );

			$cancelled_order_ids[] = array(
				'temp_id' => (string) $temp_id,
				'oid'     => (string) $cancel_id,
				'restock' => $cancel_order_params['restock'],
			);

			$response = array();

			$response['ocid'] = $offline_change['cancel_order']['ocid'];

			echo wp_json_encode( $response );
		} elseif ( ! empty( $offline_change['refund_order'] ) ) {
			$refund_order_params = $offline_change['refund_order'];
			$temp_id             = $refund_order_params['oid'];
			$order_id            = $refund_order_params['oid'];
			$refunded_items      = json_decode( stripslashes( $refund_order_params['refundedItems'] ), true );

			foreach ( $refunded_items as &$refunded_item ) {
				if ( ! empty( $created_updated_orders[ $temp_id ] ) ) {
					foreach ( $created_updated_orders[ $temp_id ] as $created_updated_order_item ) {
						if ( $created_updated_order_item['oipid'] === $refunded_item['oipid'] ) {
							$refunded_item['oiid'] = $created_updated_order_item['oiid'];

							break;
						}
					}
				}
			}

			foreach ( $created_updated_order_ids as $created_updated_order_id ) {
				if ( $created_updated_order_id['temp_id'] === $temp_id ) {
					$order_id = $created_updated_order_id['oid'];

					break;
				}
			}

			$refund_result = fooeventspos_do_refund_order( $order_id, $refunded_items );

			$response = array();

			$response['ocid'] = $offline_change['refund_order']['ocid'];

			if ( ! empty( $refund_result['square_refund'] ) ) {

				if ( 'error' === $refund_result['square_refund'] ) {

					$result['square_refund'] = 'error';

				}
			}

			echo wp_json_encode( $response );
		}

		if ( $offline_change !== $last_offline_change ) {
			echo '|';
		}
	}

	echo 'FooEventsPOSResponse:';

	echo wp_json_encode( $result );
}

/**
 * Cancel a WooCommerce order.
 *
 * @since 1.0.0
 * @param int     $order_id The WooCommerce order ID.
 * @param boolean $restock Whether or not the items should be restocked.
 * @param string  $platform The platform that is currently performing this request.
 *
 * @return bool Cancelled order.
 */
function fooeventspos_do_cancel_order( $order_id, $restock, $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	try {
		$wc_order = wc_get_order( $order_id );

		if ( false === $wc_order ) {
			return false;
		}

		if ( false === $restock ) {
			$wc_order_items = $wc_order->get_items();

			foreach ( $wc_order_items as $wc_order_item ) {
				$product_variation_id = $wc_order_item['product_id'];

				if ( $wc_order_item['variation_id'] > 0 ) {
					$product_variation_id = $wc_order_item['variation_id'];
				}

				$wc_product = wc_get_product( $product_variation_id );

				if ( false !== $wc_product && $wc_product->get_manage_stock() ) {
					wc_update_product_stock( $wc_product, $wc_order_item->get_quantity(), 'decrease' );
				}

				$wc_product = null;

				unset( $wc_product );
			}
		}

		if ( $wc_order->update_status( 'cancelled', '', false ) ) {
			return true;
		} else {
			return false;
		}
	} catch ( Exception $e ) {
		return false;
	}

	return false;
}

/**
 * Refund a WooCommerce order and restock items.
 *
 * @since 1.0.0
 * @param int    $order_id The WooCommerce order ID.
 * @param array  $refunded_items Items to be refunded.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Refund order result.
 */
function fooeventspos_do_refund_order( $order_id, $refunded_items, $platform = 'any' ) {

	fooeventspos_update_modified_date( $order_id );

	$wc_order = wc_get_order( $order_id );

	$refund_args = array(
		'order_id' => $order_id,
	);

	$refund_total = 0.0;

	$line_items    = array();
	$restock_items = array();

	foreach ( $refunded_items as $refunded_item ) {
		$refund_total += (float) $refunded_item['refund_total'] + ( ! empty( $refunded_item['refund_tax'] ) ? (float) $refunded_item['refund_tax'] : 0.0 );

		if ( (int) $refunded_item['restock_qty'] > 0 ) {
			$restock_items[ (string) $refunded_item['oipid'] ] = $refunded_item['restock_qty'];
		}

		$line_item = array(
			'qty'          => $refunded_item['qty'],
			'refund_total' => $refunded_item['refund_total'],
		);

		if ( ! empty( $refunded_item['refund_tax'] ) && ! empty( $refunded_item['refund_taxes'] ) ) {

			$line_item['refund_tax'] = array();

			foreach ( $refunded_item['refund_taxes'] as $refund_tax_rate_id => $refund_tax_rate_total ) {
				$line_item['refund_tax'][ $refund_tax_rate_id ] = $refund_tax_rate_total;
			}
		}

		$line_items[ $refunded_item['oiid'] ] = $line_item;

		unset( $line_item );
	}

	$payment_method_key = $wc_order->get_meta( '_fooeventspos_payment_method', true );

	if ( round( $wc_order->get_total() ) === round( $wc_order->get_total_refunded() + $refund_total ) ) {
		if ( ! empty( $restock_items ) ) {
			$_POST['restock_refunded_items'] = 'on';
		}

		$wc_order->update_status( 'refunded', '', false );

		$payments_json = $wc_order->get_meta( '_fooeventspos_payments', true );

		if ( 'fooeventspos_split' === $payment_method_key ) {
			if ( '' !== $payments_json ) {
				$payments = json_decode( $payments_json, true );

				if ( false !== $payments && ! empty( $payments ) ) {
					foreach ( $payments as &$payment ) {
						if ( '1' !== $payment['par'] ) {
							$payment['par'] = '1';

							$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment );

							if ( ! is_wp_error( $payment_post ) ) {
								$payment['fspid'] = (string) $payment_post['ID'];
								$payment['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
								$payment['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
								$payment['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );
							}
						}
					}

					$payments_json = wp_json_encode( $payments );
				}
			}
		} elseif ( '' !== $payments_json ) {
				$payments = json_decode( $payments_json, true );

			if ( false !== $payments && ! empty( $payments ) ) {
				$payment = $payments[0];

				$payment['par'] = '1';

				$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment );

				if ( ! is_wp_error( $payment_post ) ) {
					$payment['fspid'] = (string) $payment_post['ID'];
					$payment['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
					$payment['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
					$payment['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );
				}

				$payments_json = wp_json_encode( array( $payment ) );
			}
		} else {
			$payment_args = array(
				'pd'   => $wc_order->get_date_created()->getTimestamp(),
				'oid'  => $wc_order->get_id(),
				'opmk' => $payment_method_key,
				'oud'  => $wc_order->get_meta( '_fooeventspos_user_id', true ),
				'tid'  => '' !== $square_order_id ? $square_order_id : ( '' !== $stripe_payment_id ? $stripe_payment_id : '' ),
				'pa'   => $wc_order->get_total(),
				'np'   => '1',
				'pn'   => '1',
				'pap'  => '1',
				'par'  => '1',
			);

			$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment_args );

			if ( ! is_wp_error( $payment_post ) ) {
				$payment_args['fspid'] = (string) $payment_post['ID'];
				$payment_args['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
				$payment_args['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
				$payment_args['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

				$payments_json = wp_json_encode( array( $payment_args ) );
			}
		}

		$wc_order->update_meta_data( '_fooeventspos_payments', $payments_json );

		ob_start();
		$wc_order->save();
		ob_end_clean();
	} else {
		if ( ! empty( $restock_items ) ) {
			$_POST['restock_refunded_items'] = 'true';
		}

		$refund_args['amount']     = $refund_total;
		$refund_args['line_items'] = $line_items;

		$refund = wc_create_refund( $refund_args );
	}

	foreach ( $restock_items as $product_id => $quantity ) {
		$wc_product = wc_get_product( $product_id );

		wc_update_product_stock( $wc_product, $quantity, 'increase' );

		unset( $wc_product );
	}

	$result = array();

	if ( in_array(
		$payment_method_key,
		array(
			'fooeventspos_square',
			'fooeventspos_square_manual',
			'fooeventspos_square_terminal',
			'fooeventspos_square_reader',
			'fooeventspos_stripe_manual',
			'fooeventspos_stripe_reader',
			'fooeventspos_stripe_chipper',
			'fooeventspos_stripe_wisepad',
			'fooeventspos_stripe_reader_m2',
		),
		true
	) ) {
		$square_stripe_refund_args = array(
			'payment_method_key'       => $payment_method_key,
			'square_order_auto_refund' => '1' === $wc_order->get_meta( '_fooeventspos_square_order_auto_refund', true ),
			'transaction_id'           => '' !== $wc_order->get_meta( '_fooeventspos_square_order_id', true ) ? $wc_order->get_meta( '_fooeventspos_square_order_id', true ) : $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ),
			'refund_total'             => $refund_total,
		);

		$result = fooeventspos_refund_square_stripe( $square_stripe_refund_args, $platform );
	}

	$result['order'] = $wc_order;

	return $result;
}

/**
 * Refund a payment.
 *
 * @since 1.8.0
 * @param array  $payment The payment array.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Refund Square or Stripe payment result.
 */
function fooeventspos_do_refund_payment( $payment = array(), $platform = 'any' ) {

	$result = array();

	if ( ! empty( $payment ) ) {
		$payment_method_key = $payment['opmk'];

		if ( in_array(
			$payment_method_key,
			array(
				'fooeventspos_square',
				'fooeventspos_square_manual',
				'fooeventspos_square_terminal',
				'fooeventspos_square_reader',
				'fooeventspos_stripe_manual',
				'fooeventspos_stripe_reader',
				'fooeventspos_stripe_chipper',
				'fooeventspos_stripe_wisepad',
				'fooeventspos_stripe_reader_m2',
			),
			true
		) ) {
			$square_stripe_refund_args = array(
				'payment_method_key'       => $payment_method_key,
				'square_order_auto_refund' => '1' === $payment['soar'],
				'transaction_id'           => $payment['tid'],
				'refund_total'             => $payment['pa'],
				'payment'                  => $payment,
			);

			$result = fooeventspos_refund_square_stripe( $square_stripe_refund_args, $platform );
		}
	}

	return $result;
}

/**
 * Refund a Square or Stripe payment.
 *
 * @since 1.8.0
 * @global wpdb $wpdb
 * @param array  $refund_args The refund arguments.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array The refund result.
 */
function fooeventspos_refund_square_stripe( $refund_args = array(), $platform = 'any' ) {
	global $wpdb;

	$result = array( 'status' => 'success' );

	if ( ! empty( $refund_args ) ) {
		require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

		$payment_method_key       = $refund_args['payment_method_key'];
		$square_order_auto_refund = $refund_args['square_order_auto_refund'];
		$refund_total             = $refund_args['refund_total'];
		$requires_card_refund     = false;

		if ( in_array(
			$payment_method_key,
			array(
				'fooeventspos_square',
				'fooeventspos_square_manual',
				'fooeventspos_square_terminal',
				'fooeventspos_square_reader',
			),
			true
		) ) {
			if ( $square_order_auto_refund ) {
				$square_order_id = $refund_args['transaction_id'];

				$refund_result = fooeventspos_refund_square_order( $square_order_id, $refund_total, $platform );

				$result['square_refund'] = $refund_result['status'];

				if ( 'success' !== $result['square_refund'] && 'fooeventspos_square_terminal' === $payment_method_key ) {
					if ( 'CARD_PRESENCE_REQUIRED' === $result['square_refund'] ) {
						// Refund via Terminal.
						$requires_card_refund = true;

						$checkout = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
							$wpdb->prepare(
								"SELECT * FROM {$wpdb->prefix}fooeventspos_square_checkouts WHERE order_id = %s",
								$square_order_id
							),
							'ARRAY_A'
						);

						if ( ! empty( $checkout ) ) {
							$refund_data = array(
								'refund' => array(
									'amount_money' => array(
										'amount'   => (int) round( $refund_total * 100 ),
										'currency' => $checkout['currency'],
									),
									'payment_id'   => $checkout['payment_id'],
									'device_id'    => $checkout['device_id'],
									'reason'       => sprintf( $fooeventspos_phrases['description_square_terminal_refund_reason'], $order_id ),
								),
							);

							$create_refund_result = fooeventspos_do_create_square_terminal_refund( $refund_data, $platform );

							if ( 'success' === $create_refund_result['status'] ) {
								$result['square_refund']          = 'terminal_refund';
								$result['square_terminal_refund'] = $create_refund_result['square_terminal_refund'];
							}
						}
					} elseif ( ! empty( $refund_result['message'] ) ) {
						$result['square_refund_message'] = $refund_result['message'];
					}
				}

				if ( ! empty( $refund_result['message'] ) ) {
					$result['square_refund_message'] = $refund_result['message'];
				}
			} else {
				$result['status']        = 'error';
				$result['square_refund'] = 'error';
			}
		} elseif ( in_array(
			$payment_method_key,
			array(
				'fooeventspos_stripe_manual',
				'fooeventspos_stripe_reader',
				'fooeventspos_stripe_chipper',
				'fooeventspos_stripe_wisepad',
				'fooeventspos_stripe_reader_m2',
			),
			true
		) ) {
			$stripe_payment_id = $refund_args['transaction_id'];

			$refund_result = fooeventspos_refund_stripe_payment( $stripe_payment_id, $refund_total );

			$result['stripe_refund'] = $refund_result['status'];
			$result['message']       = $refund_result['message'];
			$result['charge_id']     = ! empty( $refund_result['charge_id'] ) ? $refund_result['charge_id'] : '';
			$result['amount']        = ! empty( $refund_result['amount'] ) ? $refund_result['amount'] : '';

			$requires_card_refund = '' !== $result['charge_id'] && '' !== $result['amount'];
		}

		// Update payment post and order payments JSON.
		if ( ! empty( $refund_args['payment'] ) && false === $requires_card_refund ) {
			$payment = $refund_args['payment'];

			$payment['par'] = '1';

			$update_result = fooeventspos_do_update_payment( $payment );

			$result['payment_update_status'] = $update_result['status'];
		}
	}

	return $result;
}

/**
 * Update a payment.
 *
 * @param array $payment The payment data to be updated.
 *
 * @return array The result of updating the payment.
 */
function fooeventspos_do_update_payment( $payment = array() ) {
	$result = array( 'status' => 'success' );

	$received_payment_post_id = $payment['fspid'];
	$payment_post_id          = $payment['fspid'];

	$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment );

	if ( ! is_wp_error( $payment_post ) ) {
		$payment['fspid'] = (string) $payment_post['ID'];
		$payment['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
		$payment['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
		$payment['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

		$payment_post_id = $payment['fspid'];
	}

	$wc_order = wc_get_order( $payment['oid'] );

	$payments_json = $wc_order->get_meta( '_fooeventspos_payments', true );

	if ( '' === $payments_json ) {
		// Generate new order payments JSON.
		$order_payments = new WP_Query(
			array(
				'post_type'      => array( 'fooeventspos_payment' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_order_id',
						'value' => $payment['oid'],
					),
				),
			)
		);

		if ( $order_payments->have_posts() ) {
			// Regenerate order payments meta from existing payment posts.
			$order_payments_array = array();

			foreach ( $order_payments->posts as $order_payment_id ) {
				if ( $current_payment['fspid'] === $payment_post_id || $current_payment['fspid'] === $received_payment_post_id ) {
					$order_payments_array[] = $payment;
				} else {
					$order_payments_array[] = array(
						'pd'    => (string) strtotime( get_post_datetime( $order_payment_id, 'date', 'gmt' )->format( 'Y-m-d H:i:s+00:00' ) ),
						'oid'   => (string) $order_id,
						'opmk'  => get_post_meta( $order_payment_id, '_payment_method_key', true ),
						'oud'   => get_post_meta( $order_payment_id, '_cashier', true ),
						'tid'   => get_post_meta( $order_payment_id, '_transaction_id', true ),
						'pa'    => get_post_meta( $order_payment_id, '_amount', true ),
						'np'    => get_post_meta( $order_payment_id, '_number_of_payments', true ),
						'pn'    => get_post_meta( $order_payment_id, '_payment_number', true ),
						'pap'   => get_post_meta( $order_payment_id, '_payment_paid', true ),
						'par'   => get_post_meta( $order_payment_id, '_payment_refunded', true ),
						'pe'    => get_post_meta( $order_payment_id, '_payment_extra', true ),
						'soar'  => get_post_meta( $order_payment_id, '_fooeventspos_square_order_auto_refund', true ),
						'sfa'   => get_post_meta( $order_payment_id, '_fooeventspos_square_fee_amount', true ),
						'fspid' => (string) $order_payment_id,
					);
				}
			}

			$payments_json = wp_json_encode( $order_payments_array );
		} else {
			$payments_json = wp_json_encode( array( $payment ) );
		}
	} else {
		// Update current order payments JSON.
		$current_payments = json_decode( $payments_json, true );

		if ( false !== $current_payments && ! empty( $current_payments ) ) {
			$updated_payments = array();

			foreach ( $current_payments as $current_payment ) {
				if ( $current_payment['fspid'] === $payment_post_id || $current_payment['fspid'] === $received_payment_post_id ) {
					$updated_payments[] = $payment;
				} else {
					$updated_payments[] = $current_payment;
				}
			}

			$payments_json = wp_json_encode( $updated_payments );
		}
	}

	$wc_order->update_meta_data( '_fooeventspos_payments', $payments_json );

	ob_start();
	$wc_order->save();
	ob_end_clean();

	return $result;
}

/**
 * Creates a new customer or updates the customer's details if they exist.
 *
 * @since 1.0.0
 * @param array  $customer_details Key/value pairs of customer data to create or update a customer.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Create or update customer result.
 */
function fooeventspos_do_create_update_customer( $customer_details = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array( 'status' => 'error' );

	$customer_id = $customer_details['cid'];

	if ( 0 === (int) $customer_id ) {
		// New customer.
		if ( false !== email_exists( $customer_details['ce'] ) ) {
			$result['message'] = 'Email exists';

			return $result;
		}
	}

	$customer_fields = array(
		'cbfn' => 'billing_first_name',
		'cbln' => 'billing_last_name',
		'cbco' => 'billing_company',
		'cba1' => 'billing_address_1',
		'cba2' => 'billing_address_2',
		'cbc'  => 'billing_city',
		'cbpo' => 'billing_postcode',
		'cbcu' => 'billing_country',
		'cbs'  => 'billing_state',
		'cbph' => 'billing_phone',
		'cbe'  => 'billing_email',
		'csfn' => 'shipping_first_name',
		'csln' => 'shipping_last_name',
		'csco' => 'shipping_company',
		'csa1' => 'shipping_address_1',
		'csa2' => 'shipping_address_2',
		'csc'  => 'shipping_city',
		'cspo' => 'shipping_postcode',
		'cscu' => 'shipping_country',
		'css'  => 'shipping_state',
		'csph' => 'shipping_phone',
	);

	$customer_data = array(
		'ID'         => $customer_id,
		'user_email' => $customer_details['ce'],
		'first_name' => $customer_details['cfn'],
		'last_name'  => $customer_details['cln'],
	);

	if ( 0 === (int) $customer_id ) {
		// New customer.
		$random_password = wp_generate_password( 12, false );

		remove_all_actions( 'user_register' );

		$customer_id = wp_create_user( $customer_details['ce'], $random_password, $customer_details['ce'] );

		update_post_meta( $customer_id, '_fooeventspos_user_source', 'fooeventspos_app' );

		$customer_data['ID']   = $customer_id;
		$customer_data['role'] = 'customer';
	}

	$customer_id = wp_update_user( $customer_data );

	if ( is_wp_error( $customer_id ) ) {

		$result['message'] = 'Unknown';

	} else {

		foreach ( $customer_fields as $key => $meta_key ) {

			update_user_meta( $customer_id, $meta_key, $customer_details[ $key ] );

		}

		$result['status'] = 'success';
		$result['cid']    = (string) $customer_id;

	}

	return $result;
}

/**
 * Gets the discount of a given coupon code for the current cart.
 *
 * @since 1.0.0
 * @param array  $coupons An array of coupon codes to apply to the order.
 * @param array  $order_items The cart items which will be used to obtain the discounts.
 * @param array  $order_customer The cart customer which will be used to obtain the discounts.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Order discounts.
 */
function fooeventspos_do_get_coupon_code_discounts( $coupons = array(), $order_items = array(), $order_customer = array(), $platform = 'any' ) {

	$output = array( 'status' => 'error' );

	WC()->frontend_includes();
	WC()->session = new WC_Session_Handler();
	WC()->session->init();
	WC()->customer = new WC_Customer( 0, true );
	WC()->cart     = new WC_Cart();
	WC()->cart->empty_cart();

	$wc_order = wc_create_order();

	// Order customer.
	if ( '' !== $order_customer['cid'] && (int) $order_customer['cid'] > 0 ) {
		fooeventspos_set_wc_customer( $order_customer );

		$wc_order->set_customer_id( (int) $order_customer['cid'] );

		if ( trim( $order_customer['cbfn'] ) !== '' ) {
			$wc_order->set_billing_first_name( $order_customer['cbfn'] );
		} else {
			$wc_order->set_billing_first_name( $order_customer['cfn'] );
		}

		if ( trim( $order_customer['cbln'] ) !== '' ) {
			$wc_order->set_billing_last_name( $order_customer['cbln'] );
		} else {
			$wc_order->set_billing_last_name( $order_customer['cln'] );
		}

		$wc_order->set_billing_company( $order_customer['cbco'] );
		$wc_order->set_billing_address_1( $order_customer['cba1'] );
		$wc_order->set_billing_address_2( $order_customer['cba2'] );
		$wc_order->set_billing_city( $order_customer['cbc'] );
		$wc_order->set_billing_postcode( $order_customer['cbpo'] );
		$wc_order->set_billing_country( $order_customer['cbcu'] );
		$wc_order->set_billing_state( $order_customer['cbs'] );
		$wc_order->set_billing_phone( $order_customer['cbph'] );
		$wc_order->set_billing_email( $order_customer['cbe'] );

		$wc_order->set_shipping_first_name( $order_customer['csfn'] );
		$wc_order->set_shipping_last_name( $order_customer['csln'] );
		$wc_order->set_shipping_company( $order_customer['csco'] );
		$wc_order->set_shipping_address_1( $order_customer['csa1'] );
		$wc_order->set_shipping_address_2( $order_customer['csa2'] );
		$wc_order->set_shipping_city( $order_customer['csc'] );
		$wc_order->set_shipping_postcode( $order_customer['cspo'] );
		$wc_order->set_shipping_country( $order_customer['cscu'] );
		$wc_order->set_shipping_state( $order_customer['css'] );
		$wc_order->set_shipping_phone( $order_customer['csph'] );
	}

	// Order date.
	$wc_order->set_date_created( time() );

	// Order items.
	foreach ( $order_items as $order_item ) {
		$line_total_excl = $order_item['oilst'];

		$product_args = array(
			'subtotal' => $line_total_excl,
			'total'    => $line_total_excl,
		);

		$wc_product = wc_get_product( $order_item['pid'] );

		$wc_order->add_product( $wc_product, $order_item['oiq'], $product_args );

		$product_args = null;

		unset( $product_args );
	}

	$wc_order->calculate_totals();

	// Coupon codes.
	if ( ! empty( $coupons ) ) {
		foreach ( $coupons as $coupon ) {
			$coupon_result = $wc_order->apply_coupon( new WC_Coupon( $coupon ) );

			if ( is_wp_error( $coupon_result ) ) {
				$wc_order->delete( true );

				$output['message'] = html_entity_decode( wp_strip_all_tags( $coupon_result->get_error_message() ), ENT_QUOTES, 'UTF-8' );

				return $output;
			}
		}
	}

	$output['status']           = 'success';
	$output['discounted_order'] = fooeventspos_do_get_single_order( $wc_order, $platform );

	$output['discounts'] = array();

	$coupons = $wc_order->get_items( 'coupon' );

	if ( ! empty( $coupons ) ) {
		foreach ( $coupons as $coupon ) {
			$output['discounts'][] = array(
				'coupon'       => $coupon->get_code(),
				'discount'     => $coupon->get_discount(),
				'discount_tax' => $coupon->get_discount_tax(),
			);

			$coupon_result = $wc_order->remove_coupon( $coupon->get_code() );
		}
	}

	WC()->session->destroy_session();
	WC()->cart->empty_cart();

	$wc_order->delete( true );

	$wc_order = null;

	unset( $wc_order );

	return $output;
}

/**
 * Gets the products and orders that were updated since the provided timestamp and return
 * the products' new prices and stock quantities and new and/or updated orders.
 *
 * @since 1.0.0
 * @param int    $last_checked_timestamp The timestamp of the last time updates were fetched by the app.
 * @param array  $order_ids_to_check An array of order IDs to check for updates.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Data updates.
 */
function fooeventspos_do_get_data_updates( $last_checked_timestamp = 0, $order_ids_to_check = array(), $platform = 'any' ) {

	if ( 0 === $last_checked_timestamp ) {
		$last_checked_timestamp = time();
	}

	$timestamp_offset        = ( 30 * 60 );
	$last_checked_timestamp -= $timestamp_offset;

	$output = array(
		'status' => 'success',
		'ts_now' => gmdate( 'Y-m-d H:i:s+00:00', time() ),
		'ts_var' => gmdate( 'Y-m-d H:i:s+00:00', $last_checked_timestamp + $timestamp_offset ),
		'ts_set' => gmdate( 'Y-m-d H:i:s+00:00', $last_checked_timestamp ),
	);

	$updated_order_product_ids = array();
	$force_hide_in_pos_ids     = array();

	// Get order updates.
	$only_load_pos_orders = 'yes' === get_option( 'globalFooEventsPOSOnlyLoadPOSOrders', '' );

	$order_load_statuses = get_option( 'globalFooEventsPOSOrderLoadStatuses', array( 'completed', 'cancelled', 'refunded' ) );

	if ( empty( $order_load_statuses ) ) {
		$order_load_statuses = array( 'completed', 'cancelled', 'refunded' );
	}

	array_walk(
		$order_load_statuses,
		function ( &$value ) {
			$value = 'wc-' . $value;
		}
	);

	// Updated order IDs.
	$updated_order_ids_args = array(
		'limit'         => -1,
		'type'          => 'shop_order',
		'status'        => $order_load_statuses,
		'date_modified' => '>' . $last_checked_timestamp,
		'return'        => 'ids',
		'orderby'       => 'ID',
	);

	if ( $only_load_pos_orders ) {
		$updated_order_ids_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array(
				'key'   => '_fooeventspos_order_source',
				'value' => 'fooeventspos_app',
			),
			array(
				'key'   => 'Order Source',
				'value' => 'FooEvents POS app',
			),
		);
	}

	if ( ! empty( $order_ids_to_check ) ) {
		$updated_order_ids_args['post__in'] = $order_ids_to_check;
	}

	$updated_order_ids = wc_get_orders( $updated_order_ids_args );

	// New order IDs.
	$new_order_ids_args = array(
		'limit'        => -1,
		'type'         => 'shop_order',
		'status'       => $order_load_statuses,
		'date_created' => '>' . $last_checked_timestamp,
		'return'       => 'ids',
		'orderby'      => 'ID',
	);

	if ( $new_order_ids_args ) {
		$updated_order_ids_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'OR',
			array(
				'key'   => '_fooeventspos_order_source',
				'value' => 'fooeventspos_app',
			),
			array(
				'key'   => 'Order Source',
				'value' => 'FooEvents POS app',
			),
		);
	}

	$new_order_ids = wc_get_orders( $new_order_ids_args );

	$order_ids = array_merge( $updated_order_ids, $new_order_ids );

	$order_updates = array();

	foreach ( $order_ids as $order_id ) {

		$wc_order = wc_get_order( $order_id );

		$updated_order = fooeventspos_do_get_single_order( $wc_order, $platform );

		foreach ( $updated_order['oi'] as $updated_order_item ) {
			$product_id = $updated_order_item['oipid'];

			$wc_product = wc_get_product( $product_id );

			if ( false !== $wc_product ) {
				if ( 'variation' === $wc_product->get_type() ) {
					$product_id = $wc_product->get_parent_id();

					$wc_product = wc_get_product( $product_id );
				}

				$updated_order_product_ids[] = (int) $product_id;

				$should_force_hide_in_pos = false;

				if ( 'cat' === (string) get_option( 'globalFooEventsPOSProductsToDisplay', '' ) ) {
					$product_categories = $wc_product->get_category_ids();

					if ( ! empty( $product_categories ) && empty( array_intersect( (array) get_option( 'globalFooEventsPOSProductCategories', array() ), $product_categories ) ) ) {
						$should_force_hide_in_pos = true;
					}
				}

				if ( 'yes' === (string) get_option( 'globalFooEventsPOSProductsOnlyInStock', '' ) && ! $wc_product->is_in_stock() ) {
					$should_force_hide_in_pos = true;
				}

				if ( $should_force_hide_in_pos ) {
					$force_hide_in_pos_ids[] = (int) $product_id;
				}
			}
		}

		$order_updates[] = $updated_order;

		$updated_order = null;

		$wc_order = null;

		unset( $wc_order, $updated_order );

	}

	$output['order_updates'] = $order_updates;

	// Get product updates.
	$product_statuses = get_option( 'globalFooEventsPOSProductsStatus', array( 'publish' ) );

	if ( empty( $product_statuses ) ) {
		$product_statuses = array( 'publish' );
	}

	$product_args = array(
		'post_type'      => 'product',
		'date_query'     => array(
			'column' => 'post_modified',
			'after'  => gmdate( 'Y-m-d H:i:s+00:00', $last_checked_timestamp ),
		),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'post_status'    => $product_statuses,
	);

	if ( 'cat' === (string) get_option( 'globalFooEventsPOSProductsToDisplay', '' ) ) {
		$product_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'product_cat',
				'terms'    => get_option( 'globalFooEventsPOSProductCategories' ),
				'operator' => 'IN',
			),
		);
	}

	$product_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery

	if ( 'yes' === (string) get_option( 'globalFooEventsPOSProductsOnlyInStock', '' ) ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'   => '_stock_status',
				'value' => 'instock',
			),
		);
	}

	$products_query = new WP_Query( $product_args );

	$wc_tax = new WC_Tax();

	$cat_names = array();

	$shop_tax = ( (string) get_option( 'woocommerce_calc_taxes', '' ) === 'yes' ) ? (string) get_option( 'woocommerce_tax_display_shop', '' ) : 'incl';

	$product_updates = array();

	$updated_order_product_ids = array_unique( array_merge( $products_query->posts, $updated_order_product_ids ) );

	foreach ( $updated_order_product_ids as $product_id ) {

		if ( 'ios' === $platform || 'android' === $platform || 'web' === $platform ) {
			if ( isset( $updated_product['fee'] ) && ! empty( $updated_product['fee'] ) ) {
				$event_type = $updated_product['fee']['et'];

				if ( 'bookings' === $event_type || 'seating' === $event_type ) {
					continue;
				}
			}
		}

		$force_hide_in_pos = in_array( (int) $product_id, $force_hide_in_pos_ids, true );

		$updated_product = fooeventspos_do_get_single_product( $product_id, $wc_tax, $cat_names, $shop_tax, $force_hide_in_pos, $platform );

		$load_product_images = get_option( 'globalFooEventsPOSProductsLoadImages', 'yes' );

		if ( 'yes' === $load_product_images ) {
			$product_image = preg_replace_callback(
				'/[^\x20-\x7f]/',
				function ( $product_image_matches ) {
					return rawurlencode( $product_image_matches[0] );
				},
				(string) get_the_post_thumbnail_url( $product_id, 'thumbnail' )
			);

			$updated_product['pi'] = $product_image;
		}

		$product_updates[] = $updated_product;

		unset( $updated_product );

	}

	$output['product_updates'] = $product_updates;

	// Get updated sale product IDs.
	$output['sale_product_ids'] = implode( ',', wc_get_product_ids_on_sale() );

	// Get customer updates.
	$args = array(
		'role__in'      => get_option( 'globalFooEventsPOSCustomerUserRole', array( 'customer', 'subscriber' ) ),
		'number'        => -1,
		'fields'        => 'ids',
		'no_found_rows' => true,
		'orderby'       => array( 'user_email', 'user_login' ),
		'order'         => 'ASC',
		'date_query'    => array(
			'after' => gmdate( 'Y-m-d H:i:s', $last_checked_timestamp ),
		),
	);

	$query = new WP_User_Query( $args );

	$args = null;

	unset( $args );

	$customer_updates = array();

	$customer_ids = $query->get_results();

	foreach ( $customer_ids as $customer_id ) {

		$customer_updates[] = fooeventspos_get_single_customer( $customer_id, $platform );

	}

	$query = null;

	unset( $query );

	$output['customer_updates'] = $customer_updates;

	return $output;
}

/**
 * Check that the user has permission to access FooEvents POS.
 *
 * @since 1.0.0
 * @param WP_User $user The WordPress user object.
 *
 * @return bool User has permission.
 */
function fooeventspos_checkroles( $user ) {
	return user_can( $user, 'publish_fooeventspos' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown
}

/**
 * Get Square order.
 *
 * @since 1.0.0
 * @param string $square_order_id The ID of the Square order.
 *
 * @return array Square order result.
 */
function fooeventspos_get_square_order( $square_order_id = '' ) {

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || '' === $square_order_id ) {
		return $result;
	}

	$response = wp_remote_get(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/orders/' . $square_order_id,
		array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['order'] ) ) {

				$result['order']  = $response_array['order'];
				$result['status'] = 'success';

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Get Square payment.
 *
 * @since 1.8.0
 * @param string $square_payment_id The ID of the Square payment.
 *
 * @return array Square payment result.
 */
function fooeventspos_get_square_payment( $square_payment_id = '' ) {

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || '' === $square_payment_id ) {
		return $result;
	}

	$response = wp_remote_get(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/payments/' . $square_payment_id,
		array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['payment'] ) ) {

				$result['payment'] = $response_array['payment'];
				$result['status']  = 'success';

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Refund a Square order.
 *
 * @since 1.0.0
 * @param string $square_order_id The ID of the Square order.
 * @param double $amount The amount in cents to refund to the original payment card.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Square refund result.
 */
function fooeventspos_refund_square_order( $square_order_id = '', $amount = 0, $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || '' === $square_order_id || 0 === $amount ) {
		return $result;
	}

	$order_result = fooeventspos_get_square_order( $square_order_id );

	if ( 'success' === $order_result['status'] ) {

		$order = $order_result['order'];

		if ( ! empty( $order['tenders'] ) ) {

			$payment_id = $order['tenders'][0]['id'];

			$refund_args = array(
				'idempotency_key' => fooeventspos_generate_idempotency_string(),
				'payment_id'      => $payment_id,
				'amount_money'    => array(
					'currency' => $order['tenders'][0]['amount_money']['currency'],
					'amount'   => (int) round( (float) $amount * 100.0 ),
				),
			);

			$response = wp_remote_post(
				'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/refunds',
				array(
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 10,
					'httpversion' => '1.1',
					'headers'     => array(
						'Authorization' => 'Bearer ' . $square_access_token,
						'Content-type'  => 'application/json',
					),
					'body'        => wp_json_encode( $refund_args ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $result;
			} else {
				$response_array = json_decode( $response['body'], true );

				if ( false !== $response_array ) {

					if ( ! empty( $response_array['refund'] ) ) {

						$result['status'] = 'success';

					} elseif ( ! empty( $response_array['errors'] ) ) {
						if ( ! empty( $response_array['errors'][0]['code'] ) ) {
							$result['status'] = $response_array['errors'][0]['code'];
						}

						if ( ! empty( $response_array['errors'][0]['detail'] ) ) {
							$result['message'] = $response_array['errors'][0]['detail'];
						}
					}
				} else {

					return $result;

				}
			}
		} else {

			$result['split_tenders'] = true;

		}
	}

	return $result;
}

/**
 * Get Square locations.
 *
 * @since 1.0.0
 *
 * @return array Square locations.
 */
function fooeventspos_get_square_locations() {

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token ) {
		return $result;
	}

	$response = wp_remote_get(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/locations',
		array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['locations'] ) ) {

				$result['status'] = 'success';

				$result['locations'] = $response_array['locations'];

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Create a Square manual payment.
 *
 * @since 1.0.0
 * @param array  $payment_data Key/value pairs for the payment data.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Created Square payment result.
 */
function fooeventspos_do_create_square_payment( $payment_data = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || empty( $payment_data ) ) {

		return $result;
	}

	$payment_data['autocomplete']    = true;
	$payment_data['idempotency_key'] = fooeventspos_generate_idempotency_string();

	$response = wp_remote_post(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/payments',
		array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
			'body'        => wp_json_encode( $payment_data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['payment'] ) ) {

				$result['status'] = 'success';

				$result['payment'] = $response_array['payment'];

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Generate a Square device code.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param array  $square_location Key/value pairs of Square location data.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Square device code result.
 */
function fooeventspos_do_generate_square_device_code( $square_location = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || empty( $square_location ) ) {

		return $result;

	}

	$device_code_data = array(
		'idempotency_key' => fooeventspos_generate_idempotency_string(),
		'device_code'     => array(
			'product_type' => 'TERMINAL_API',
			'location_id'  => $square_location['id'],
			'name'         => $square_location['name'] . ' - TERMINAL',
		),
	);

	$response = wp_remote_post(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/devices/codes',
		array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
			'body'        => wp_json_encode( $device_code_data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['device_code'] ) ) {

				$result['status'] = 'success';

				$result['device_code'] = $response_array['device_code'];

				$table_name = $wpdb->prefix . 'fooeventspos_square_devices';

				$existing_location_row = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT id FROM {$wpdb->prefix}fooeventspos_square_devices WHERE location_id = %s",
						$square_location['id']
					)
				);

				$device_data = array(
					'device_code_id' => $result['device_code']['id'],
					'code'           => $result['device_code']['code'],
					'location_id'    => $result['device_code']['location_id'],
					'pair_by'        => $result['device_code']['pair_by'],
					'status'         => $result['device_code']['status'],
				);

				if ( null === $existing_location_row ) {

					// Insert new location device.
					$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$table_name,
						$device_data
					);

				} else {

					// Update existing location device.
					$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$table_name,
						$device_data,
						array( 'id' => $existing_location_row )
					);

				}
			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Get the pair status of a Square device.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param string $square_device_code_id Square device ID.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Square device pair status result.
 */
function fooeventspos_do_get_square_device_pair_status( $square_device_code_id = '', $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	if ( '' === $square_device_code_id ) {

		return $result;

	}

	$square_device = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fooeventspos_square_devices WHERE device_code_id = %s",
			$square_device_code_id
		),
		'ARRAY_A'
	);

	if ( ! empty( $square_device ) ) {

		$result['status']    = $square_device['status'];
		$result['device_id'] = $square_device['device_id'];

	}

	return $result;
}

/**
 * Create a Square terminal checkout request.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param array  $checkout_data Key/value pairs of checkout data.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Create Square terminal checkout result.
 */
function fooeventspos_do_create_square_terminal_checkout( $checkout_data = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || empty( $checkout_data ) ) {

		return $result;
	}

	$checkout_data['idempotency_key'] = fooeventspos_generate_idempotency_string();

	$response = wp_remote_post(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/terminals/checkouts',
		array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
			'body'        => wp_json_encode( $checkout_data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['checkout'] ) ) {

				$result['status'] = 'success';

				$result['checkout'] = $response_array['checkout'];

				$checkout_created_timestamp = strtotime( $result['checkout']['created_at'] );
				$checkout_deadline          = gmdate( DATE_RFC3339, $checkout_created_timestamp + ( 5 * 60 ) );

				$table_name = $wpdb->prefix . 'fooeventspos_square_checkouts';

				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$table_name,
					array(
						'checkout_id' => $result['checkout']['id'],
						'amount'      => (string) ( $result['checkout']['amount_money']['amount'] / 100.0 ),
						'currency'    => $result['checkout']['amount_money']['currency'],
						'created_at'  => $result['checkout']['created_at'],
						'device_id'   => $result['checkout']['device_options']['device_id'],
						'deadline'    => $checkout_deadline,
						'status'      => $result['checkout']['status'],
						'updated_at'  => $result['checkout']['updated_at'],
					)
				);

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Get a Square terminal checkout status.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param string $checkout_id The ID of the Square checkout.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Square terminal checkout status result.
 */
function fooeventspos_do_get_square_terminal_checkout_status( $checkout_id = '', $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || '' === $checkout_id ) {

		return $result;

	}

	$checkout = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fooeventspos_square_checkouts WHERE checkout_id = %s",
			$checkout_id
		),
		'ARRAY_A'
	);

	if ( empty( $checkout ) ) {

		return $result;

	}

	$result['status'] = $checkout['status'];

	if ( 'COMPLETED' === $checkout['status'] ) {

		$payment_id = $checkout['payment_id'];

		$response = wp_remote_get(
			'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/payments/' . $payment_id,
			array(
				'method'      => 'GET',
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'headers'     => array(
					'Authorization' => 'Bearer ' . $square_access_token,
					'Content-type'  => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $result;
		} else {
			$response_array = json_decode( $response['body'], true );

			if ( false !== $response_array ) {

				if ( ! empty( $response_array['payment'] ) ) {

					$result['soid'] = $response_array['payment']['order_id'];

					$table_name = $wpdb->prefix . 'fooeventspos_square_checkouts';

					$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
						$table_name,
						array(
							'order_id' => $result['soid'],
						),
						array(
							'payment_id' => $payment_id,
						)
					);

				} else {

					return $result;

				}
			} else {

				return $result;

			}
		}
	}

	return $result;
}

/**
 * Create a Square terminal refund request.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param array  $refund_data Key/value pairs of refund data.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Create Square terminal refund result.
 */
function fooeventspos_do_create_square_terminal_refund( $refund_data = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || empty( $refund_data ) ) {

		return $result;
	}

	$refund_data['idempotency_key'] = fooeventspos_generate_idempotency_string();

	$response = wp_remote_post(
		'https://connect.squareup' . ( false !== strpos( $square_application_id, 'sandbox-' ) ? 'sandbox' : '' ) . '.com/v2/terminals/refunds',
		array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $square_access_token,
				'Content-type'  => 'application/json',
			),
			'body'        => wp_json_encode( $refund_data ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $result;
	} else {
		$response_array = json_decode( $response['body'], true );

		if ( false !== $response_array ) {

			if ( ! empty( $response_array['refund'] ) ) {

				$result['status'] = 'success';

				$result['square_terminal_refund'] = $response_array['refund'];

				$refund_created_timestamp = strtotime( $response_array['refund']['created_at'] );
				$refund_deadline          = gmdate( DATE_RFC3339, $refund_created_timestamp + ( 5 * 60 ) );

				$table_name = $wpdb->prefix . 'fooeventspos_square_refunds';

				$refund_db_data = array(
					'refund_id'  => $response_array['refund']['id'],
					'amount'     => (string) ( $response_array['refund']['amount_money']['amount'] / 100.0 ),
					'currency'   => $response_array['refund']['amount_money']['currency'],
					'device_id'  => $response_array['refund']['device_id'],
					'deadline'   => $refund_deadline,
					'payment_id' => $response_array['refund']['payment_id'],
					'order_id'   => $response_array['refund']['order_id'],
					'status'     => $response_array['refund']['status'],
					'created_at' => $response_array['refund']['created_at'],
					'updated_at' => $response_array['refund']['updated_at'],
				);

				$insert_result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$table_name,
					$refund_db_data
				);

			} else {

				return $result;

			}
		} else {

			return $result;

		}
	}

	return $result;
}

/**
 * Get a Square terminal refund status.
 *
 * @since 1.0.0
 * @global wpdb $wpdb
 * @param string $refund_id The ID of the Square refund.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Square terminal refund status result.
 */
function fooeventspos_do_get_square_terminal_refund_status( $refund_id = '', $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	global $wpdb;

	$result = array( 'status' => 'error' );

	$square_application_id = get_option( 'globalFooEventsPOSSquareApplicationID' );
	$square_access_token   = get_option( 'globalFooEventsPOSSquareAccessToken' );

	if ( '' === $square_application_id || '' === $square_access_token || '' === $refund_id ) {

		return $result;

	}

	$refund = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fooeventspos_square_refunds WHERE refund_id = %s",
			$refund_id
		),
		'ARRAY_A'
	);

	if ( empty( $refund ) ) {

		return $result;

	}

	$result['status'] = $refund['status'];

	return $result;
}

/**
 * Register a Stripe reader.
 *
 * @since 1.0.0
 * @param array  $reader_data The reader data in associative array.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Register Stripe reader result.
 */
function fooeventspos_do_register_stripe_reader( $reader_data = array(), $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key ||
		empty( $reader_data ) || (
			! empty( $reader_data ) && (
				'' === $reader_data['location'] ||
				'' === $reader_data['registration_code']
			)
		)
	) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$stripe_reader = $stripe->terminal->readers->create( $reader_data );

		$result = array(
			'status' => 'success',
			'reader' => $stripe_reader,
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Create a Stripe connection token.
 *
 * @since 1.0.0
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Create Stripe connection token result.
 */
function fooeventspos_do_create_stripe_connection_token( $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key ) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$connection_token = $stripe->terminal->connectionTokens->create( array() );

		$result = array(
			'status' => 'success',
			'secret' => $connection_token['secret'],
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Create a Stripe payment intent.
 *
 * @since 1.0.0
 * @param array  $payment_intent_data An array containing the payment intent data.
 * @param string $payment_method The FooEvents POS payment method.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Create Stripe payment intent result.
 */
function fooeventspos_do_create_stripe_payment_intent( $payment_intent_data = array(), $payment_method = '', $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || empty( $payment_intent_data ) || '' === $payment_method ) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_intent_data['payment_method_types'] = array( 'card' );
		$payment_intent_data['capture_method']       = 'manual';

		if ( 'fooeventspos_stripe_reader' === $payment_method || 'fooeventspos_stripe_chipper' === $payment_method || 'fooeventspos_stripe_wisepad' === $payment_method || 'fooeventspos_stripe_reader_m2' === $payment_method ) {
			$payment_intent_data['payment_method_types'] = array( 'card_present' );

			if ( 'CAD' === strtoupper( $payment_intent_data['currency'] ) ) {
				$payment_intent_data['payment_method_types'][] = 'interac_present';
			}

			$payment_intent_data['capture_method'] = 'automatic';
		}

		$payment_intent = $stripe->paymentIntents->create( $payment_intent_data ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		$result = array(
			'status'         => 'success',
			'payment_intent' => $payment_intent,
		);

	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Add WooCommerce order number to a Stripe payment intent.
 *
 * @since 1.9.0
 * @param string $payment_intent_id The payment intent ID.
 * @param string $wc_order_number The WooCommerce order number.
 *
 * @return array Add WooCommerce order number to Stripe payment intent result.
 */
function fooeventspos_add_wc_order_number_to_stripe_payment( $payment_intent_id = '', $wc_order_number = '' ) {

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || '' === $payment_intent_id || '' === $wc_order_number ) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_intent = $stripe->paymentIntents->update( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$payment_intent_id,
			array( 'metadata' => array( 'woocommerce_order_number' => $wc_order_number ) )
		);

		$result = array(
			'status'         => 'success',
			'payment_intent' => $payment_intent,
		);

	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Capture a processed Stripe payment intent.
 *
 * @since 1.0.0
 * @param string $payment_intent_id The payment intent ID.
 * @param string $platform The platform that is currently performing this request.
 *
 * @return array Capture Stripe payment result.
 */
function fooeventspos_do_capture_stripe_payment( $payment_intent_id = '', $platform = 'any' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || '' === $payment_intent_id ) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_intent = $stripe->paymentIntents->capture( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$payment_intent_id,
			array()
		);

		$result = array(
			'status'         => 'success',
			'payment_intent' => $payment_intent,
		);

	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Refund a captured Stripe payment intent.
 *
 * @since 1.0.0
 * @param string $stripe_payment_id The ID of the Stripe payment intent.
 * @param double $amount The amount in cents to refund to the original payment card.
 *
 * @return array Refund Stripe payment result.
 */
function fooeventspos_refund_stripe_payment( $stripe_payment_id = '', $amount = 0 ) {

	require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

	$result = array(
		'status'  => 'error',
		'message' => '',
	);

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || '' === $stripe_payment_id || 0 === $amount ) {

		return $result;

	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_intent = $stripe->paymentIntents->retrieve( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$stripe_payment_id,
			array( 'expand' => array( 'latest_charge' ) )
		);

		if ( ( ! empty( $payment_intent['charges'] ) && ! empty( $payment_intent['charges']['data'] ) ) || ! empty( $payment_intent['latest_charge'] ) ) {
			$charge              = ! empty( $payment_intent['latest_charge'] ) ? $payment_intent['latest_charge'] : $payment_intent['charges']['data'][0];
			$payment_method_type = $charge['payment_method_details']['type'];

			if ( 'interac_present' === $payment_method_type ) {
				$result = array(
					'status'    => 'reader_refund',
					'charge_id' => $charge['id'],
					'amount'    => (int) round( (float) $amount * 100.0 ),
				);
			} else {
				$refund = $stripe->refunds->create(
					array(
						'payment_intent' => $stripe_payment_id,
						'amount'         => (int) round( (float) $amount * 100.0 ),
					)
				);

				$result = array(
					'status' => 'success',
					'refund' => $refund,
				);
			}
		} else {
			$result['message'] = sprintf( $fooeventspos_phrases['text_stripe_payment_intent_error'], $stripe_payment_id );
		}
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = sprintf( $fooeventspos_phrases['text_stripe_payment_intent_error'], $stripe_payment_id ) . ' - ' . $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = sprintf( $fooeventspos_phrases['text_stripe_payment_intent_error'], $stripe_payment_id ) . ' - ' . $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = sprintf( $fooeventspos_phrases['text_stripe_payment_intent_error'], $stripe_payment_id ) . ' - ' . $e->getMessage();
	}

	return $result;
}

/**
 * Get Stripe locations.
 *
 * @since 1.0.0
 *
 * @return array Stripe locations.
 */
function fooeventspos_get_stripe_locations() {

	$result = array( 'status' => 'error' );

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key ) {
		return $result;
	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$stripe_locations = $stripe->terminal->locations->all( array() );

		$result = array(
			'status'    => 'success',
			'locations' => $stripe_locations['data'],
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Get Stripe readers.
 *
 * @since 1.0.0
 *
 * @return array Stripe readers.
 */
function fooeventspos_get_stripe_readers() {

	$result = array( 'status' => 'error' );

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key ) {
		return $result;
	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$stripe_readers = $stripe->terminal->readers->all( array() );

		$result = array(
			'status'  => 'success',
			'readers' => $stripe_readers['data'],
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Get Stripe payment intent.
 *
 * @since 1.8.0
 * @param string $payment_intent_id The Stripe payment intent ID.
 *
 * @return array Stripe payment intent.
 */
function fooeventspos_get_stripe_payment_intent( $payment_intent_id = '' ) {

	$result = array( 'status' => 'error' );

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || '' === $payment_intent_id ) {
		return $result;
	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_intent = $stripe->paymentIntents->retrieve( $payment_intent_id, array() ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		$result = array(
			'status'         => 'success',
			'payment_intent' => $payment_intent,
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Get Stripe payment method.
 *
 * @since 1.8.0
 * @param string $payment_method_id The Stripe payment method ID.
 *
 * @return array Stripe payment method.
 */
function fooeventspos_get_stripe_payment_method( $payment_method_id = '' ) {

	$result = array( 'status' => 'error' );

	$stripe_secret_key = get_option( 'globalFooEventsPOSStripeSecretKey' );

	if ( '' === $stripe_secret_key || '' === $payment_method_id ) {
		return $result;
	}

	fooeventspos_load_stripe_lib();

	try {
		$stripe = new \FooEventsPOS\Stripe\StripeClient( $stripe_secret_key );

		$payment_method = $stripe->paymentMethods->retrieve( // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$payment_method_id,
			array()
		);

		$result = array(
			'status'         => 'success',
			'payment_method' => $payment_method,
		);
	} catch ( \FooEventsPOS\Stripe\Exception\ApiErrorException $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Exception $e ) {
		$result['message'] = $e->getMessage();
	} catch ( Error $e ) {
		$result['message'] = $e->getMessage();
	}

	return $result;
}

/**
 * Generate an idempotency string.
 *
 * @since 1.0.0
 *
 * @return string Idempotency string.
 */
function fooeventspos_generate_idempotency_string() {
	return fooeventspos_generate_random_string( 8 ) . '-' . fooeventspos_generate_random_string( 4 ) . '-' . fooeventspos_generate_random_string( 4 ) . '-' . fooeventspos_generate_random_string( 4 ) . '-' . fooeventspos_generate_random_string( 12 );
}

/**
 * Generate a random string.
 *
 * @since 1.0.0
 * @param int $length The length of the randomly generated string.
 *
 * @return string Random string.
 */
function fooeventspos_generate_random_string( $length = 4 ) {

	$characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characters_length = strlen( $characters );
	$random_string     = '';

	for ( $i = 0; $i < $length; $i++ ) {

		$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];

	}

	return $random_string;
}

/**
 * Load Stripe library.
 *
 * @since 1.0.0
 */
function fooeventspos_load_stripe_lib() {

	$fooeventspos_stripe_path = '';

	$fooeventspos_stripe_path = plugin_dir_path( __FILE__ ) . 'vendor/stripe-php/';

	require_once $fooeventspos_stripe_path . 'init.php';
}

/**
 * Return all the app icon sizes.
 *
 * @since 1.1.0
 *
 * @return array Icon sizes.
 */
function fooeventspos_get_app_icon_sizes() {
	return array(
		'android-chrome-192x192.png'       => 192,
		'android-chrome-512x512.png'       => 512,
		'apple-touch-icon-precomposed.png' => 180,
		'apple-touch-icon.png'             => 180,
		'favicon-16x16.png'                => 16,
		'favicon-32x32.png'                => 32,
		'mstile-70x70.png'                 => 70,
		'mstile-144x144.png'               => 144,
		'mstile-150x150.png'               => 150,
		'mstile-310x310.png'               => 310,
	);
}

/**
 * Return all the available order statuses
 *
 * @since 1.3.0
 * @param string $filter The filter used to remove certain statuses.
 *
 * @return array Order statuses.
 */
function fooeventspos_get_all_order_statuses( $filter = 'load' ) {
	$order_statuses = array();

	if ( function_exists( 'wc_get_order_statuses' ) ) {

		$wc_order_statuses = wc_get_order_statuses();

		$exclude_statuses = array(
			'load'       => array(
				'failed',
				'checkout-draft',
			),
			'submit'     => array(
				'cancelled',
				'refunded',
				'failed',
				'checkout-draft',
			),
			'incomplete' => array(
				'cancelled',
				'refunded',
				'failed',
				'checkout-draft',
			),
		);

		foreach ( $wc_order_statuses as $key => $value ) {
			$new_key = str_replace( 'wc-', '', $key );

			if ( in_array( $new_key, $exclude_statuses[ $filter ], true ) ) {
				continue;
			}

			$order_statuses[ $new_key ] = $value;
		}
	}

	return $order_statuses;
}

/**
 * Convert a PHP date format string into another date format
 *
 * @since 1.2.0
 * @param string $format The PHP date format converted into another date format.
 * @param string $language The language to convert the date format to.
 *
 * @return string Date format.
 */
function fooeventspos_convert_php_date_format( $format, $language = 'js' ) {

	$return_format = 'yyyy-mm-dd';

	$formats = array(
		'D d-m-y'         => array(
			'js'      => 'ddd dd-mm-yyyy',
			'ios'     => 'EEE dd-MM-yyyy',
			'android' => 'EEE dd-MM-yyyy',
		),
		'D d-m-Y'         => array(
			'js'      => 'ddd dd-mm-yyyy',
			'ios'     => 'EEE dd-MM-yyyy',
			'android' => 'EEE dd-MM-yyyy',
		),
		'l d-m-Y'         => array(
			'js'      => 'dddd dd-mm-yyyy',
			'ios'     => 'EEEE dd-MM-yyyy',
			'android' => 'EEEE dd-MM-yyyy',
		),
		'jS F Y'          => array(
			'js'      => 'd mmmm yyyy',
			'ios'     => 'd MMMM yyyy',
			'android' => 'd MMMM yyyy',
		),
		'F j, Y'          => array(
			'js'      => 'mmmm dd, yyyy',
			'ios'     => 'MMMM dd, yyyy',
			'android' => 'MMMM dd, yyyy',
		),
		'F j Y'           => array(
			'js'      => 'mmmm dd yyyy',
			'ios'     => 'MMMM dd yyyy',
			'android' => 'MMMM dd yyyy',
		),
		'M. j, Y'         => array(
			'js'      => 'mmm. dd, yyyy',
			'ios'     => 'MMM. dd, yyyy',
			'android' => 'MMM. dd, yyyy',
		),
		'M j, Y'          => array(
			'js'      => 'mmm dd, yyyy',
			'ios'     => 'MMM dd, yyyy',
			'android' => 'MMM dd, yyyy',
		),
		'M. d, Y'         => array(
			'js'      => 'mmm. dd, yyyy',
			'ios'     => 'MMM. dd, yyyy',
			'android' => 'MMM. dd, yyyy',
		),
		'M d, Y'          => array(
			'js'      => 'mmm dd, yyyy',
			'ios'     => 'MMM dd, yyyy',
			'android' => 'MMM dd, yyyy',
		),
		'mm/dd/yyyy'      => array(
			'js'      => 'mm/dd/yyyy',
			'ios'     => 'mm/dd/yyyy',
			'android' => 'mm/dd/yyyy',
		),
		'j F Y'           => array(
			'js'      => 'd mmmm yyyy',
			'ios'     => 'd MMMM yyyy',
			'android' => 'd MMMM yyyy',
		),
		'Y/m/d'           => array(
			'js'      => 'yyyy/mm/dd',
			'ios'     => 'yyyy/MM/dd',
			'android' => 'yyyy/MM/dd',
		),
		'm/d/Y'           => array(
			'js'      => 'mm/dd/yyyy',
			'ios'     => 'MM/dd/yyyy',
			'android' => 'MM/dd/yyyy',
		),
		'd/m/Y'           => array(
			'js'      => 'dd/mm/yyyy',
			'ios'     => 'dd/MM/yyyy',
			'android' => 'dd/MM/yyyy',
		),
		'Y-m-d'           => array(
			'js'      => 'yyyy-mm-dd',
			'ios'     => 'yyyy-MM-dd',
			'android' => 'yyyy-MM-dd',
		),
		'm-d-Y'           => array(
			'js'      => 'mm-dd-yyyy',
			'ios'     => 'MM-dd-yyyy',
			'android' => 'MM-dd-yyyy',
		),
		'd-m-Y'           => array(
			'js'      => 'dd-mm-yyyy',
			'ios'     => 'dd-MM-yyyy',
			'android' => 'dd-MM-yyyy',
		),
		'j. FY'           => array(
			'js'      => 'd. mmmmyyyy',
			'ios'     => 'd. MMMMyyyy',
			'android' => 'd. MMMMyyyy',
		),
		'j. F Y'          => array(
			'js'      => 'd. mmmm yyyy',
			'ios'     => 'd. MMMM yyyy',
			'android' => 'd. MMMM yyyy',
		),
		'j. F, Y'         => array(
			'js'      => 'd. mmmm, yyyy',
			'ios'     => 'd. MMMM, yyyy',
			'android' => 'd. MMMM, yyyy',
		),
		'j.m.Y'           => array(
			'js'      => 'd.mm.yyyy',
			'ios'     => 'd.MM.yyyy',
			'android' => 'd.MM.yyyy',
		),
		'j.n.Y'           => array(
			'js'      => 'd.m.yyyy',
			'ios'     => 'd.m.yyyy',
			'android' => 'd.m.yyyy',
		),
		'j. n. Y'         => array(
			'js'      => 'd. m. yyyy',
			'ios'     => 'd. m. yyyy',
			'android' => 'd. m. yyyy',
		),
		'j.n. Y'          => array(
			'js'      => 'd.m. yyyy',
			'ios'     => 'd.m. yyyy',
			'android' => 'd.m. yyyy',
		),
		'j \d\e F \d\e Y' => array(
			'js'      => 'd \d\e mmmm \d\e yyyy',
			'ios'     => 'd \d\e MMMM \d\e yyyy',
			'android' => 'd \d\e MMMM \d\e yyyy',
		),
		'D j M Y'         => array(
			'js'      => 'ddd d mmm yyyy',
			'ios'     => 'EEE d MMM yyyy',
			'android' => 'EEE d MMM yyyy',
		),
		'D F j'           => array(
			'js'      => 'ddd mmmm d',
			'ios'     => 'EEE MMMM d',
			'android' => 'EEE MMMM d',
		),
		'l j F Y'         => array(
			'js'      => 'dddd d mmmm yyyy',
			'ios'     => 'EEEE d MMMM yyyy',
			'android' => 'EEEE d MMMM yyyy',
		),
	);

	if ( ! empty( $formats[ $format ] ) && ! empty( $formats[ $format ][ $language ] ) && '' !== $formats[ $format ][ $language ] ) {
		$return_format = $formats[ $format ][ $language ];
	}

	return $return_format;
}

/**
 * Convert a PHP time format string into another time format
 *
 * @since 1.8.5
 * @param string $format The PHP time format converted into another time format.
 * @param string $language The language to convert the date format to.
 *
 * @return string Date format.
 */
function fooeventspos_convert_php_time_format( $format, $language = 'js' ) {

	$return_format = 'hh:mm a';

	if ( 'js' === $language ) {
		$return_format = 'h:MM tt';
	}

	$formats = array(
		'g:i a'   => array(
			'js'      => 'h:MM tt',
			'ios'     => 'h:mm a',
			'android' => 'h:mm a',
		),
		'g:i A'   => array(
			'js'      => 'h:MM TT',
			'ios'     => 'h:mm a',
			'android' => 'h:mm a',
		),
		'h:i a'   => array(
			'js'      => 'hh:MM tt',
			'ios'     => 'hh:mm a',
			'android' => 'hh:mm a',
		),
		'h:i A'   => array(
			'js'      => 'hh:MM TT',
			'ios'     => 'hh:mm a',
			'android' => 'hh:mm a',
		),
		'G:i'     => array(
			'js'      => 'H:MM',
			'ios'     => 'H:mm',
			'android' => 'H:mm',
		),
		'H:i'     => array(
			'js'      => 'HH:MM',
			'ios'     => 'HH:mm',
			'android' => 'HH:mm',
		),
		'g:i:s a' => array(
			'js'      => 'h:MM:ss tt',
			'ios'     => 'h:mm:ss a',
			'android' => 'h:mm:ss a',
		),
		'g:i:s A' => array(
			'js'      => 'h:MM:ss TT',
			'ios'     => 'h:mm:ss a',
			'android' => 'h:mm:ss a',
		),
		'h:i:s a' => array(
			'js'      => 'hh:MM:ss tt',
			'ios'     => 'hh:mm:ss a',
			'android' => 'hh:mm:ss a',
		),
		'h:i:s A' => array(
			'js'      => 'hh:MM:ss TT',
			'ios'     => 'hh:mm:ss a',
			'android' => 'hh:mm:ss a',
		),
		'G:i:s'   => array(
			'js'      => 'H:MM:ss',
			'ios'     => 'H:mm:ss',
			'android' => 'H:mm:ss',
		),
		'H:i:s'   => array(
			'js'      => 'HH:MM:ss',
			'ios'     => 'HH:mm:ss',
			'android' => 'HH:mm:ss',
		),
	);

	if ( ! empty( $formats[ $format ] ) && ! empty( $formats[ $format ][ $language ] ) && '' !== $formats[ $format ][ $language ] ) {
		$return_format = $formats[ $format ][ $language ];
	}

	return $return_format;
}

/**
 * Update the modified date to the current time for the given item ID.
 *
 * @since 1.3.0
 * @param int $id The ID of the item to be updated.
 */
function fooeventspos_update_modified_date( $id = '' ) {
	global $wpdb;

	if ( (int) $id > 0 ) {
		$time = time();

		if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) && Automattic\WooCommerce\Utilities\OrderUtil::is_order( $id ) ) {
			$wc_order = wc_get_order( $id );

			$wc_order->set_date_modified( $time );
			$wc_order->save();
		} else {
			$mysql_time_format = 'Y-m-d H:i:s';

			$post_modified = gmdate( $mysql_time_format, $time );

			$post_modified_gmt = gmdate( $mysql_time_format, ( $time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );

			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"UPDATE $wpdb->posts SET post_modified = %s, post_modified_gmt = %s  WHERE ID = %s",
					$post_modified,
					$post_modified_gmt,
					$id
				)
			);
		}
	}
}

/**
 * Check stock for the provided cart products.
 *
 * @since 1.7.0
 * @param array $order_items The order items for which stock should be checked.
 *
 * @return array Check stock result.
 */
function fooeventspos_do_check_stock( $order_items = array() ) {

	require plugin_dir_path( __FILE__ ) . 'fooeventspos-phrases-helper.php';

	$result = array( 'status' => 'success' );

	if ( ! empty( $order_items ) ) {
		$stock_unavailable_array = array();
		$product_stock           = array();

		foreach ( $order_items as $product_id => $quantity ) {
			$wc_product = wc_get_product( $product_id );

			if ( false !== $wc_product ) {
				if ( 'variation' === $wc_product->get_type() ) {
					if ( false === $wc_product->get_manage_stock() ) {
						if ( 'instock' !== $wc_product->get_stock_status() ) {
							$stock_unavailable_array[] = sprintf( '%1$s - %2$s (%3$s)', $product_id, $wc_product->get_title(), $wc_product->get_attribute_summary() );

							$product_stock[ (string) $product_id ] = '';
						}
					} elseif ( 'parent' === $wc_product->get_manage_stock() ) {
						$parent_product_id = $wc_product->get_parent_id();
						$wc_parent_product = wc_get_product( $parent_product_id );

						if ( false !== $wc_parent_product ) {
							if ( false === $wc_parent_product->get_manage_stock() ) {
								if ( 'instock' !== $wc_parent_product->get_stock_status() ) {
									$stock_unavailable_array[] = sprintf( '%1$s - %2$s', $parent_product_id, $wc_product->get_title() );

									$product_stock[ (string) $product_id ] = '';
								}
							} elseif ( (int) $wc_parent_product->get_stock_quantity() < (int) $quantity && 'onbackorder' !== $wc_parent_product->get_stock_status() && false === $wc_parent_product->backorders_allowed() ) {
								$stock_unavailable_array[] = sprintf( '%1$s - %2$s', $parent_product_id, $wc_product->get_title() );

								$product_stock[ (string) $product_id ] = (string) $wc_parent_product->get_stock_quantity();
							}
						} else {
							$result = array(
								'status'  => 'error',
								'message' => $fooeventspos_phrases['text_one_or_more_products_not_found'],
							);

							break;
						}
					} elseif ( (int) $wc_product->get_stock_quantity() < (int) $quantity && 'onbackorder' !== $wc_product->get_stock_status() && false === $wc_product->backorders_allowed() ) {
						$stock_unavailable_array[] = sprintf( '%1$s - %2$s (%3$s)', $product_id, $wc_product->get_title(), $wc_product->get_attribute_summary() );

						$product_stock[ (string) $product_id ] = (string) $wc_product->get_stock_quantity();
					}
				} elseif ( false === $wc_product->get_manage_stock() ) {
					if ( 'instock' !== $wc_product->get_stock_status() ) {
						$stock_unavailable_array[] = sprintf( '%1$s - %2$s', $product_id, $wc_product->get_title() );

						$product_stock[ (string) $product_id ] = '';
					}
				} elseif ( (int) $wc_product->get_stock_quantity() < (int) $quantity && 'onbackorder' !== $wc_product->get_stock_status() && false === $wc_product->backorders_allowed() ) {
					$stock_unavailable_array[] = sprintf( '%1$s - %2$s', $product_id, $wc_product->get_title() );

					$product_stock[ (string) $product_id ] = (string) $wc_product->get_stock_quantity();
				}
			} else {
				$result = array(
					'status'  => 'error',
					'message' => $fooeventspos_phrases['text_one_or_more_products_not_found'],
				);

				break;
			}
		}

		if ( ! empty( $stock_unavailable_array ) ) {
			$result = array(
				'status'  => 'error',
				'message' => sprintf( $fooeventspos_phrases['text_products_not_enough_stock'] . "\n" . '%1$s', implode( "\n", $stock_unavailable_array ) ),
				'stock'   => $product_stock,
			);
		}
	} else {
		$result = array(
			'status'  => 'error',
			'message' => $fooeventspos_phrases['text_no_order_items_provided'],
		);
	}

	return $result;
}

/**
 * Get all available WooCommerce shipping methods.
 *
 * @since 1.7.0
 *
 * @return array Shipping methods.
 */
function fooeventspos_get_available_shipping_methods() {

	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return array();
	}

	$zones = WC_Shipping_Zones::get_zones();

	if ( ! is_array( $zones ) ) {
		return array();
	}

	$shipping_methods = array_column( $zones, 'shipping_methods' );

	$flatten = array_merge( ...$shipping_methods );

	$normalized_shipping_methods = array();

	foreach ( $flatten as $key => $class ) {
		$normalized_shipping_methods[ $class->id ] = $class->method_title;
	}

	return $normalized_shipping_methods;
}

/**
 * Check seat availability before completing checkout.
 *
 * @since 1.9.0
 * @param array $attendee_details The order's attendee details.
 *
 * @return array The output.
 */
function fooeventspos_do_check_unavailable_booking_slots( $attendee_details ) {
	$output = array( 'status' => 'error' );

	$fooevents_bookings = new FooEvents_Bookings();

	if ( ! empty( $attendee_details ) ) {
		foreach ( $attendee_details as $key => $val ) {
			$_POST[ $key ] = sanitize_text_field( $val );
		}

		$output['status']      = 'success';
		$output['unavailable'] = $fooevents_bookings->check_availablity_for_all_attendees( '', array() );
	}

	return $output;
}

/**
 * Check seat availability before completing checkout.
 *
 * @since 1.9.0
 * @param array $attendee_details The order's attendee details.
 * @param array $product_ids An array of the order product IDs for seating events.
 *
 * @return array The output.
 */
function fooeventspos_do_check_unavailable_seats( $attendee_details, $product_ids ) {
	$output = array( 'status' => 'error' );

	if ( ! empty( $attendee_details ) && ! empty( $product_ids ) ) {
		$updated_unavailable_seats = array();
		$unavailable_seats         = array();
		$updated_blocked_seats     = array();
		$blocked_seats             = array();

		foreach ( $product_ids as $event_id ) {
			$wc_product = wc_get_product( $event_id );

			$fooevents_seating_options_serialized   = $wc_product->get_meta( 'fooevents_seating_options_serialized', true );
			$fooevents_seats_blocked_serialized     = $wc_product->get_meta( 'fooevents_seats_blocked_serialized', true );
			$fooevents_seats_unavailable_serialized = $wc_product->get_meta( 'fooevents_seats_unavailable_serialized', true );

			$fooevents_seating_options   = '' === $fooevents_seating_options_serialized ? array() : json_decode( $fooevents_seating_options_serialized, true );
			$fooevents_seats_blocked     = '' === $fooevents_seats_blocked_serialized ? array() : json_decode( $fooevents_seats_blocked_serialized, true );
			$fooevents_seats_unavailable = '' === $fooevents_seats_unavailable_serialized ? array() : json_decode( $fooevents_seats_unavailable_serialized, true );

			$updated_blocked_seats[ (string) $event_id ]     = $fooevents_seats_blocked;
			$updated_unavailable_seats[ (string) $event_id ] = $fooevents_seats_unavailable;

			foreach ( $attendee_details as $key => $val ) {
				if ( false !== strpos( $key, 'fooevents_seat_number_' ) ) {
					$attendee_number = end( explode( '_', $key ) );

					if ( in_array( $val, $fooevents_seats_blocked, true ) ) {
						if ( ! isset( $blocked_seats[ (string) $event_id ] ) ) {
							$blocked_seats[ (string) $event_id ] = array();
						}

						$blocked_seats[ (string) $event_id ][] = $attendee_number;
					}

					if ( in_array( $val, $fooevents_seats_unavailable, true ) ) {
						if ( ! isset( $unavailable_seats[ (string) $event_id ] ) ) {
							$unavailable_seats[ (string) $event_id ] = array();
						}

						$unavailable_seats[ (string) $event_id ][] = $attendee_number;
					}
				}
			}
		}

		$output['status']                    = 'success';
		$output['updated_blocked_seats']     = $updated_blocked_seats;
		$output['blocked_seats']             = $blocked_seats;
		$output['updated_unavailable_seats'] = $updated_unavailable_seats;
		$output['unavailable_seats']         = $unavailable_seats;
	}

	return $output;
}
