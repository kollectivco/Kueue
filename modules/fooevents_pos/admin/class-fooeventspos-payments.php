<?php
/**
 * Payments class containing initialization of the payments custom post type as well as its functionality
 *
 * @link https://www.fooevents.com
 * @since 1.8.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The payments custom post type functionality of the plugin.
 *
 * @since 1.8.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_Payments {
	/**
	 * The FooEvents POS phrases helper.
	 *
	 * @since 1.8.0
	 * @var array $fooeventspos_phrases The current phrases helper array.
	 */
	private $fooeventspos_phrases;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.8.0
	 */
	public function __construct() {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$this->fooeventspos_phrases = $fooeventspos_phrases;

		add_action( 'manage_edit-fooeventspos_payment_columns', array( &$this, 'fooeventspos_add_payments_table_columns' ), 10, 1 );
		add_action( 'manage_fooeventspos_payment_posts_custom_column', array( &$this, 'fooeventspos_add_payments_table_content' ), 10, 1 );
		add_action( 'pre_get_posts', array( $this, 'fooeventspos_alter_query' ) );

		add_filter( 'manage_edit-fooeventspos_payment_sortable_columns', array( $this, 'fooeventspos_sortable_admin_columns' ) );
		add_filter( 'manage_posts_columns', array( $this, 'fooeventspos_custom_post_columns' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'fooeventspos_row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-fooeventspos_payment', array( $this, 'fooeventspos_remove_bulk_actions' ) );
		add_filter( 'list_table_primary_column', array( $this, 'fooeventspos_primary_column' ), 10, 2 );
		add_filter( 'query_vars', array( $this, 'fooeventspos_custom_query_vars_filter' ) );
	}

	/**
	 * Register the payments custom post type.
	 *
	 * @since 1.8.0
	 */
	public function fooeventspos_register_fooeventspos_payment_post_type() {

		$labels = array(
			'name'               => $this->fooeventspos_phrases['title_fooeventspos_pos_payments'],
			'singular_name'      => $this->fooeventspos_phrases['title_fooeventspos_pos_payment'],
			'all_items'          => $this->fooeventspos_phrases['title_fooeventspos_pos_payments'],
			'search_items'       => $this->fooeventspos_phrases['button_search_fooeventspos_pos_payments'],
			'not_found'          => $this->fooeventspos_phrases['text_no_fooeventspos_pos_payments_found'],
			'not_found_in_trash' => $this->fooeventspos_phrases['text_no_fooeventspos_pos_payments_found_trash'],
			'parent_item_colon'  => '',
			'menu_name'          => $this->fooeventspos_phrases['title_fooeventspos_pos_payments'],
		);

		$args = array(
			'labels'              => $labels,
			'description'         => $this->fooeventspos_phrases['description_fooeventspos_pos_payment'],
			'public'              => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_in_menu'        => false,
			'capability_type'     => array( 'fooeventspos_payment', 'fooeventspos_payments' ),
			'capabilities'        => array(
				'edit_post'              => 'edit_fooeventspos_payment',
				'read_post'              => 'read_fooeventspos_payment',
				'delete_post'            => 'delete_fooeventspos_payment',

				'edit_posts'             => 'edit_fooeventspos_payments',
				'edit_others_posts'      => 'edit_others_fooeventspos_payments',
				'delete_posts'           => 'delete_fooeventspos_payments',
				'publish_posts'          => false,
				'read_private_posts'     => 'read_private_fooeventspos_payment',

				'read'                   => 'read_fooeventspos_payment',
				'delete_private_posts'   => 'delete_private_fooeventspos_payments',
				'delete_published_posts' => 'delete_published_fooeventspos_payments',
				'delete_others_posts'    => 'delete_others_fooeventspos_payments',
				'edit_private_posts'     => 'edit_private_fooeventspos_payment',
				'edit_published_posts'   => 'edit_published_fooeventspos_payments',
				'create_posts'           => false,
			),
			'map_meta_cap'        => true,
			'supports'            => array( 'custom-fields' ),
		);

		register_post_type( 'fooeventspos_payment', $args );
	}

	/**
	 * Add additional columns to the payments table.
	 *
	 * @since 1.8.0
	 * @param array $columns The payments table columns.
	 *
	 * @return array $columns Payment table columns.
	 */
	public function fooeventspos_add_payments_table_columns( $columns ) {

		$columns['cb']             = $this->fooeventspos_phrases['title_pos_payment_checkbox'];
		$columns['payment_id']     = $this->fooeventspos_phrases['title_pos_payment_id'];
		$columns['order']          = $this->fooeventspos_phrases['title_pos_payment_order'];
		$columns['payment_date']   = $this->fooeventspos_phrases['title_pos_payment_date'];
		$columns['cashier']        = $this->fooeventspos_phrases['label_order_cashier'];
		$columns['payment_method'] = $this->fooeventspos_phrases['title_pos_payment_method'];
		$columns['transaction']    = $this->fooeventspos_phrases['title_pos_payment_transaction'];
		$columns['amount']         = $this->fooeventspos_phrases['title_pos_payment_amount'];

		return $columns;
	}

	/**
	 * Adds column content to the payments custom post type.
	 *
	 * @param string $column The column of the payments table.
	 * @global object $post
	 */
	public function fooeventspos_add_payments_table_content( $column ) {

		global $post;

		$payment_post_meta = get_post_meta( $post->ID );

		switch ( $column ) {
			case 'payment_id':
				$order_id = get_post_meta( $post->ID, '_order_id', true );

				echo '<a href="' . esc_attr( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ) . '" target="_blank"><strong>#' . esc_html( $post->ID ) . '</strong></a>';

				break;
			case 'order':
				$order_id     = get_post_meta( $post->ID, '_order_id', true );
				$order_number = get_post_meta( $post->ID, '_order_number', true );

				$wc_order = wc_get_order( $order_id );

				if ( false === $wc_order ) {
					esc_html( $order_id );
				} else {
					echo '<a href="' . esc_attr( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ) . '" target="_blank">' . esc_html( $order_number ) . '</a>';
				}

				break;
			case 'payment_date':
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

				echo esc_html( date_i18n( $date_format . ' ' . $time_format, strtotime( $post->post_date ) ) );

				break;
			case 'cashier':
				$cashier_id           = get_post_meta( $post->ID, '_cashier', true );
				$cashier_display_name = get_post_meta( $post->ID, '_cashier_display_name', true );

				if ( '' !== $cashier_display_name ) {
					echo '<a href="' . esc_attr( admin_url( 'edit.php?post_type=fooeventspos_payment&pos_payment_cashier=' . $cashier_id ) ) . '">' . esc_html( $cashier_display_name ) . '</a>';
				} else {
					echo '-';
				}

				break;
			case 'payment_method':
				echo esc_html( get_post_meta( $post->ID, '_payment_method_name', true ) );

				break;
			case 'transaction':
				$fooeventspos_payment_method = get_post_meta( $post->ID, '_payment_method_key', true );
				$transaction_id          = get_post_meta( $post->ID, '_transaction_id', true );
				$payment_extra           = get_post_meta( $post->ID, '_payment_extra', true );

				if ( '' !== $payment_extra ) {
					echo esc_html( $payment_extra );
				}

				// Square payment.
				if ( in_array(
					$fooeventspos_payment_method,
					array(
						'fooeventspos_square',
						'fooeventspos_square_manual',
						'fooeventspos_square_terminal',
						'fooeventspos_square_reader',
					),
					true
				) ) {
					if ( '' !== $transaction_id ) {
						if ( '' !== $payment_extra ) {
							echo '<br/>';
						}

						echo '<a href="https://squareup.com/dashboard/sales/transactions/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';

						$square_fees = get_post_meta( $post->ID, '_fooeventspos_square_fee_amount', true );

						if ( (float) $square_fees > 0 ) {

							echo '<br/>' . wp_kses_post( $this->fooeventspos_phrases['text_square_fees'] . ': ' . wc_price( $square_fees ) );

						}
					}
				}

				// Stripe payment.
				if ( in_array(
					$fooeventspos_payment_method,
					array(
						'fooeventspos_stripe_manual',
						'fooeventspos_stripe_reader',
						'fooeventspos_stripe_chipper',
						'fooeventspos_stripe_wisepad',
						'fooeventspos_stripe_reader_m2',
					),
					true
				) ) {
					if ( '' !== $transaction_id ) {
						if ( '' !== $payment_extra ) {
							echo '<br/>';
						}

						echo '<a href="https://dashboard.stripe.com/payments/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
					}
				}

				if ( '' === $transaction_id && '' === $payment_extra ) {
					echo '-';
				}

				break;
			case 'amount':
				$payment_refunded = '1' === get_post_meta( $post->ID, '_payment_refunded', true );

				if ( $payment_refunded ) {
					echo wp_kses_post( wc_price( 0 ) );

					echo '&nbsp;<s>';
				}

				echo wp_kses_post( wc_price( get_post_meta( $post->ID, '_amount', true ) ) );

				if ( $payment_refunded ) {
					echo '</s>';
				}

				if ( (int) get_post_meta( $post->ID, '_number_of_payments', true ) > 1 ) {
					$order_id = get_post_meta( $post->ID, '_order_id', true );

					printf( ' <a href="' . esc_attr( admin_url( 'edit.php?post_type=fooeventspos_payment&pos_payment_order=' . $order_id ) ) . '">(' . esc_html( $this->fooeventspos_phrases['text_pos_payment_number'] ) . ')</a>', esc_html( get_post_meta( $post->ID, '_payment_number', true ) ), esc_html( get_post_meta( $post->ID, '_number_of_payments', true ) ) );
				}

				break;
		}
	}

	/**
	 * Specify which columns should be sortable in the payments table.
	 *
	 * @since 1.8.0
	 * @param array $columns The payments table columns.
	 *
	 * @return array $columns Sortable payment table columns.
	 */
	public function fooeventspos_sortable_admin_columns( $columns ) {

		$columns['payment_id']     = 'payment_id';
		$columns['order']          = 'order';
		$columns['payment_date']   = 'payment_date';
		$columns['cashier']        = 'cashier';
		$columns['payment_method'] = 'payment_method';
		$columns['amount']         = 'amount';

		return $columns;
	}

	/**
	 * Customize admin table columns for the payments custom post type.
	 *
	 * @since 1.8.0
	 * @param array  $columns The admin table columns.
	 * @param string $post_type The post type for the admin table.
	 */
	public function fooeventspos_custom_post_columns( $columns, $post_type ) {

		if ( 'fooeventspos_payment' === $post_type ) {

			unset(
				$columns['title'],
				$columns['date'],
				$columns['stats']
			);
		}

		return $columns;
	}

	/**
	 * Remove payment table row actions.
	 *
	 * @since 1.8.0
	 * @param array   $actions The current row actions.
	 * @param WP_Post $post The current post object.
	 *
	 * @return array The updated payment table row actions.
	 */
	public function fooeventspos_row_actions( $actions, $post ) {
		if ( 'fooeventspos_payment' === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['edit'] );
		}

		return $actions;
	}

	/**
	 * Remove payment table bulk actions.
	 *
	 * @since 1.8.0
	 * @param array $actions The current row actions.
	 *
	 * @return array The updated payment table bulk actions.
	 */
	public function fooeventspos_remove_bulk_actions( $actions ) {
		unset( $actions['edit'] );

		return $actions;
	}

	/**
	 * Set the payment ID column to be the primary column.
	 *
	 * @since 1.8.0
	 * @param string $default_column The current default column.
	 * @param string $screen The current screen.
	 *
	 * @return string The default column.
	 */
	public function fooeventspos_primary_column( $default_column, $screen ) {
		if ( 'edit-fooeventspos_payment' === $screen ) {
			$default_column = 'payment_id';
		}

		return $default_column;
	}

	/**
	 * Customize query vars for post filter.
	 *
	 * @since 1.8.0
	 * @param array $vars The query vars.
	 *
	 * @return array The updated query vars.
	 */
	public function fooeventspos_custom_query_vars_filter( $vars ) {
		if ( isset( $_GET['pos_payment_order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$vars[] .= 'pos_payment_order';
		} elseif ( isset( $_GET['pos_payment_cashier'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$vars[] .= 'pos_payment_cashier';
		}

		return $vars;
	}

	/**
	 * Alter post query based on POS payments filters and sorting.
	 *
	 * @since 1.8.0
	 * @param array $query The current posts query.
	 */
	public function fooeventspos_alter_query( $query ) {
		if ( ! is_admin() || ! isset( $query->query['post_type'] ) || ( isset( $query->query['post_type'] ) && 'fooeventspos_payment' !== $query->query['post_type'] ) ) {
			return;
		}

		// Payments filters.
		if ( isset( $query->query_vars['pos_payment_order'] ) ) {
			$query->set( 'meta_key', '_order_id' );
			$query->set( 'meta_value', $query->query_vars['pos_payment_order'] );
		} elseif ( isset( $query->query_vars['pos_payment_cashier'] ) ) {
			$query->set( 'meta_key', '_cashier' );
			$query->set( 'meta_value', $query->query_vars['pos_payment_cashier'] );
		}

		// Order payments by.
		if ( '' === $query->get( 'orderby' ) || 'payment_id' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'ID' );
		} elseif ( 'order' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_order_id' );
		} elseif ( 'cashier' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_cashier_display_name' );
		} elseif ( 'payment_method' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_payment_method_name' );
		} elseif ( 'amount' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_key', '_amount' );
		}

		// Payments sort order.
		if ( '' === $query->get( 'order' ) ) {
			$query->set( 'order', 'DESC' );
		}

		// Search payments.
		if ( $query->is_search() && '' !== $query->get( 's' ) ) {
			$search_term = $query->get( 's' );
			$query->set( 's', '' );

			$meta_query = array(
				'relation' => 'OR',
				array(
					'key'     => '_payment_id',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_payment_date',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_order_id',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_order_number',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_payment_method_key',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_payment_method_name',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_cashier',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_cashier_display_name',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_transaction_id',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_payment_extra',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_amount',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
			);

			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Create or update a payment post.
	 *
	 * @since 1.8.0
	 * @param array $payment_args The args for the payment post.
	 *
	 * @return array|WP_Error Payment post array or error.
	 */
	public static function fooeventspos_create_update_payment( $payment_args = array() ) {
		$payment_post    = null;
		$payment_post_id = '';

		if ( isset( $payment_args['fspid'] ) && ! empty( $payment_args['fspid'] ) ) {
			$payment_post_id = $payment_args['fspid'];

			$payment_post = get_post( $payment_post_id, 'ARRAY_A' );
		}

		if ( null === $payment_post ) {
			// First generate new payment post.
			$rand = wp_rand( 111111, 999999 );

			$payment_post = array(
				'post_title'  => 'FooEvents POS Payment ' . $rand,
				'post_status' => 'publish',
				'post_type'   => 'fooeventspos_payment',
			);

			if ( ! empty( $payment_args['pd'] ) ) {
				$payment_post['post_date_gmt'] = gmdate( 'Y-m-d H:i:s+00:00', (int) $payment_args['pd'] );
			}

			$payment_post['ID'] = wp_insert_post( $payment_post, true );

			if ( is_wp_error( $payment_post['ID'] ) ) {
				return $payment_post['ID'];
			}

			$payment_post['post_title'] = '#' . $payment_post['ID'];

			$payment_post_id = (string) wp_update_post( $payment_post );

			$new_payment_post = get_post( $payment_post_id, 'ARRAY_A' );

			$payment_post['post_date'] = $new_payment_post['post_date'];

			update_post_meta( $payment_post_id, '_payment_id', $payment_post_id );
		}

		if ( '' === $payment_post_id ) {
			return new WP_Error( 'error' );
		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		update_post_meta( $payment_post_id, '_payment_date', date_i18n( $date_format . ' ' . $time_format, strtotime( $payment_post['post_date'] ) ) );

		update_post_meta( $payment_post_id, '_order_id', $payment_args['oid'] );

		$wc_order = wc_get_order( $payment_args['oid'] );

		update_post_meta( $payment_post_id, '_order_number', $wc_order->get_order_number() );
		update_post_meta( $payment_post_id, '_payment_method_key', $payment_args['opmk'] );

		$payment_methods = fooeventspos_do_get_all_payment_methods( true );

		update_post_meta( $payment_post_id, '_payment_method_name', $payment_methods[ $payment_args['opmk'] ] );
		update_post_meta( $payment_post_id, '_cashier', $payment_args['oud'] );

		$fooeventspos_user_name = '';

		if ( '' !== $payment_args['oud'] ) {
			$fooeventspos_user = get_userdata( $payment_args['oud'] );

			if ( false !== $fooeventspos_user ) {
				$fooeventspos_user_name = $fooeventspos_user->display_name;
			}
		}

		$payment_extra = '';

		// Square payment.
		if ( ! empty( $payment_args['tid'] ) && in_array(
			$payment_args['opmk'],
			array(
				'fooeventspos_square',
				'fooeventspos_square_manual',
				'fooeventspos_square_terminal',
				'fooeventspos_square_reader',
			),
			true
		) ) {
			$square_order_result = fooeventspos_get_square_order( $payment_args['tid'] );

			if ( 'success' === $square_order_result['status'] ) {

				$square_order = $square_order_result['order'];

				if ( ! empty( $square_order['tenders'] ) ) {
					$payment_square_fees = 0.0;

					foreach ( $square_order['tenders'] as $square_tender ) {
						if ( isset( $square_tender['processing_fee_money'] ) ) {
							$payment_square_fees += ( (float) ( $square_tender['processing_fee_money']['amount'] ) / 100.0 );
						}
					}

					update_post_meta( $payment_post_id, '_fooeventspos_square_fee_amount', $payment_square_fees );

					if ( count( $square_order['tenders'] ) === 1 ) {
						update_post_meta( $payment_post_id, '_fooeventspos_square_order_auto_refund', '1' );

						$square_payment_id = $square_order['tenders'][0]['id'];

						$square_payment_result = fooeventspos_get_square_payment( $square_payment_id );

						if ( 'success' === $square_payment_result['status'] ) {
							$square_payment = $square_payment_result['payment'];

							if ( ! empty( $square_payment['card_details'] ) ) {
								$card = $square_payment['card_details']['card'];

								$payment_extra = strtoupper( $card['card_brand'] ) . ' ' . strtoupper( $card['card_type'] ) . ' ' . $card['last_4'];
							}
						}
					}
				}
			}
		}

		// Stripe payment.
		if ( ! empty( $payment_args['tid'] ) && in_array(
			$payment_args['opmk'],
			array(
				'fooeventspos_stripe_manual',
				'fooeventspos_stripe_reader',
				'fooeventspos_stripe_chipper',
				'fooeventspos_stripe_wisepad',
				'fooeventspos_stripe_reader_m2',
			),
			true
		) ) {
			$stripe_payment_intent_result = fooeventspos_get_stripe_payment_intent( $payment_args['tid'] );

			if ( 'success' === $stripe_payment_intent_result['status'] ) {

				fooeventspos_add_wc_order_number_to_stripe_payment( $payment_args['tid'], $wc_order->get_order_number() );

				$payment_intent = $stripe_payment_intent_result['payment_intent'];

				if ( ! empty( $payment_intent['payment_method'] ) ) {
					$payment_method_id = $payment_intent['payment_method'];

					$stripe_payment_method_result = fooeventspos_get_stripe_payment_method( $payment_method_id );

					if ( 'success' === $stripe_payment_method_result['status'] ) {
						$stripe_payment_method = $stripe_payment_method_result['payment_method'];

						if ( ! empty( $stripe_payment_method['card'] ) || ! empty( $stripe_payment_method['card_present'] ) || ! empty( $stripe_payment_method['interac_present'] ) ) {
							$card = ! empty( $stripe_payment_method['card'] ) ? $stripe_payment_method['card'] : ( ! empty( $stripe_payment_method['card_present'] ) ? $stripe_payment_method['card_present'] : $stripe_payment_method['interac_present'] );

							$payment_extra = strtoupper( $card['brand'] ) . ' ' . strtoupper( $card['funding'] ) . ' ' . $card['last4'];
						}
					}
				}
			}
		}

		update_post_meta( $payment_post_id, '_cashier_display_name', $fooeventspos_user_name );
		update_post_meta( $payment_post_id, '_transaction_id', $payment_args['tid'] );
		update_post_meta( $payment_post_id, '_payment_extra', $payment_extra );
		update_post_meta( $payment_post_id, '_amount', $payment_args['pa'] );
		update_post_meta( $payment_post_id, '_number_of_payments', $payment_args['np'] );
		update_post_meta( $payment_post_id, '_payment_number', $payment_args['pn'] );
		update_post_meta( $payment_post_id, '_payment_paid', $payment_args['pap'] );
		update_post_meta( $payment_post_id, '_payment_refunded', $payment_args['par'] );

		return $payment_post;
	}
}
