<?php
/**
 * HTML template for displaying POS payments in an order meta box
 *
 * @link https://www.fooevents.com
 * @since 1.8.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>
<table class="fooeventspos-payments-meta-table">
	<tr>
		<th><?php echo esc_html( $fooeventspos_phrases['title_pos_payment_id'] ); ?></th>
		<th><?php echo esc_html( $fooeventspos_phrases['title_pos_payment_date'] ); ?></th>
		<th><?php echo esc_html( $fooeventspos_phrases['label_order_cashier'] ); ?></th>
		<th><?php echo esc_html( $fooeventspos_phrases['title_pos_payment_method'] ); ?></th>
		<th><?php echo esc_html( $fooeventspos_phrases['title_pos_payment_transaction'] ); ?></th>
		<th><?php echo esc_html( $fooeventspos_phrases['title_pos_payment_amount'] ); ?></th>
	</tr>
	<?php
	foreach ( $fooeventspos_payments as $payment_data ) {
		?>
		<tr>
			<td valign="top"><?php echo esc_html( '#' . $payment_data['_payment_id'] ); ?></td>
			<td valign="top"><?php echo esc_html( $payment_data['_payment_date'] ); ?></td>
			<td valign="top">
			<?php
			$cashier_id           = $payment_data['_cashier'][0];
			$cashier_display_name = $payment_data['_cashier_display_name'][0];

			if ( '' !== $cashier_display_name ) {
				echo '<a href="' . esc_attr( admin_url( 'edit.php?post_type=fooeventspos_payment&pos_payment_cashier=' . $cashier_id ) ) . '" target="_blank">' . esc_html( $cashier_display_name ) . '</a>';
			} else {
				echo '-';
			}
			?>
			</td>
			<td valign="top"><?php echo esc_html( $payment_data['_payment_method_name'][0] ); ?></td>
			<td valign="top">
			<?php
			$fooeventspos_payment_method = $payment_data['_payment_method_key'][0];
			$transaction_id          = $payment_data['_transaction_id'][0];
			$payment_extra           = $payment_data['_payment_extra'][0];

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

					$square_fees = get_post_meta( $payment_data['_payment_id'], '_fooeventspos_square_fee_amount', true );

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
			?>
			</td>
			<td valign="top">
			<?php
			$payment_refunded = '1' === $payment_data['_payment_refunded'][0];

			if ( $payment_refunded ) {
				echo wp_kses_post( wc_price( 0 ) );

				echo '&nbsp;<s>';
			}

			echo wp_kses_post( wc_price( $payment_data['_amount'][0] ) );

			if ( $payment_refunded ) {
				echo '</s>';
			}
			?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
