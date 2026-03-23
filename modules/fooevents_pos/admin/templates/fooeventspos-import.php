<?php
/**
 * HTML template for the FooEvents POS importer that imports offline changes made in the FooEvents POS app
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap">
	<h1><?php echo esc_html( $fooeventspos_phrases['title_fooeventspos_import'] ); ?></h1>
	<?php
	if ( isset( $_FILES['fooeventspos-import'] ) && check_admin_referer( 'fooeventspos-import' ) ) {
		if ( isset( $_FILES['fooeventspos-import']['error'] ) && $_FILES['fooeventspos-import']['error'] > 0 ) {
			wp_die( esc_html( $fooeventspos_phrases['error_xml_import'] ) );
		} else {
			$file_name       = isset( $_FILES['fooeventspos-import']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['fooeventspos-import']['name'] ) ) : '';
			$file_name_split = explode( '.', $file_name );
			$file_ext        = strtolower( end( $file_name_split ) );

			if ( 'xml' === $file_ext ) {
				if ( ! function_exists( 'WP_Filesystem' ) ) {

					require_once ABSPATH . '/wp-admin/includes/file.php';

				}

				WP_Filesystem();

				global $wp_filesystem;

				libxml_use_internal_errors( true );

				$xml_data = $wp_filesystem->get_contents( sanitize_text_field( wp_unslash( isset( $_FILES['fooeventspos-import']['tmp_name'] ) ? $_FILES['fooeventspos-import']['tmp_name'] : '' ) ) );
				$xml      = simplexml_load_string( $xml_data );

				if ( false === $xml || 'fooeventspos_changes' !== $xml->getName() ) {
					echo "<div class='notice notice-error is-dismissible'><p>" . esc_html( $fooeventspos_phrases['description_unable_to_read_xml'] ) . '</p></div>';
					?>
					<p>
					<?php
					foreach ( libxml_get_errors() as $import_error ) {
						printf(
							'<strong>%s %s, %s %s:</strong> <em>%s</em><br/>',
							esc_html( $fooeventspos_phrases['label_line'] ),
							esc_html( $import_error->line ),
							esc_html( $fooeventspos_phrases['label_column'] ),
							esc_html( $import_error->column ),
							esc_html( $import_error->message )
						);
					}
					?>
					</p>
					<?php
				} else {
					$total_changes = count( $xml->children() );

					if ( $total_changes > 0 ) {
						$fooeventspos_offline_changes = array();
						$offline_changes          = get_option( 'globalFooEventsPOSOfflineChanges' );

						if ( false !== $offline_changes ) {
							$fooeventspos_offline_changes = json_decode( get_option( 'globalFooEventsPOSOfflineChanges' ), true );
						}
						?>
						<h3><?php echo esc_html( $fooeventspos_phrases['title_importing_offline_changes'] ); ?></h3>
						<?php
						ob_start();

						printf(
							"%s: %d\n\n",
							esc_html( $fooeventspos_phrases['label_offline_changes_imported'] ),
							esc_html( $total_changes )
						);

						$change_count = 1;
						$failure      = false;

						foreach ( $xml->children() as $offline_change ) {
							$offline_change_id   = $offline_change->offline_change_id->__toString();
							$offline_change_type = $offline_change->offline_change_type->__toString();

							$already_imported = in_array( $offline_change_id, $fooeventspos_offline_changes, true ) !== false;

							$import_title  = '';
							$import_result = '';

							if ( 'update_product' === $offline_change_type ) {
								$update_product_params = json_decode( $offline_change->product_data->__toString(), true );

								$wc_product = wc_get_product( $update_product_params['pid'] );

								$import_title = esc_html( $fooeventspos_phrases['label_update_product'] ) . ' "' . (string) html_entity_decode( $wc_product->get_title(), ENT_QUOTES, 'UTF-8' ) . '" (' . esc_html( $update_product_params['pid'] ) . ')';

								if ( $already_imported ) {
									$import_result = esc_html( $fooeventspos_phrases['label_update_product_skipped'] ) . "\n";
								} else {
									$fooeventspos_offline_changes[] = $offline_change_id;

									$update_product_result = false;

									$update_product_result = fooeventspos_do_update_product( $update_product_params );

									if ( ! empty( $update_product_result ) ) {
										$import_result .= "\n";
										$import_result .= ! empty( $update_product_params['pp'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_price'] ) . ': ' . esc_html( wp_strip_all_tags( wc_price( $update_product_params['pp'] ) ) ) . "\n" : '';
										$import_result .= ! empty( $update_product_params['prp'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_regular_price'] ) . ': ' . esc_html( wp_strip_all_tags( wc_price( $update_product_params['prp'] ) ) ) . "\n" : '';
										$import_result .= ! empty( $update_product_params['psp'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_sale_price'] ) . ': ' . esc_html( wp_strip_all_tags( wc_price( $update_product_params['psp'] ) ) ) . "\n" : '';
										$import_result .= ! empty( $update_product_params['ps'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_stock'] ) . ': ' . esc_html( $update_product_params['ps'] ) . "\n" : '';
										$import_result .= ! empty( $update_product_params['pt'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_title'] ) . ': "' . esc_html( $update_product_params['pt'] ) . "\"\n" : '';
										$import_result .= ! empty( $update_product_params['psku'] ) ? "\t- " . esc_html( $fooeventspos_phrases['label_update_product_set_sku'] ) . ': "' . esc_html( $update_product_params['psku'] ) . "\"\n" : '';
									} else {
										$import_result = 'failed';

										$failure = true;
									}
								}
							} elseif ( 'cancel_order' === $offline_change_type ) {
								$order_id = $offline_change->order_id->__toString();
								$restock  = (bool) $offline_change->return_stock->__toString();

								$import_title = esc_html( $fooeventspos_phrases['label_cancel_order'] ) . ' #' . $order_id;

								if ( $already_imported ) {
									$import_result = esc_html( $fooeventspos_phrases['label_cancel_order_skipped'] ) . "\n";
								} else {
									$success = fooeventspos_do_cancel_order( $order_id, $restock );

									if ( $success ) {
										$fooeventspos_offline_changes[] = $offline_change_id;

										$import_result .= "\n";
										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_cancel_order_set_status'] ) . "\n";

										if ( $restock ) {
											$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_cancel_order_restocked_items'] ) . "\n";
										}
									} else {
										$import_result = 'failed';

										$failure = true;
									}
								}
							} elseif ( 'refund_order' === $offline_change_type ) {
								$order_id = $offline_change->order_id->__toString();
								$restock  = (bool) $offline_change->return_stock->__toString();

								$import_title = esc_html( $fooeventspos_phrases['label_refund_order'] ) . ' #' . $order_id;

								if ( $already_imported ) {
									$import_result = esc_html( $fooeventspos_phrases['label_refund_order_skipped'] ) . "\n";
								} else {
									$refunded_items = array();

									$import_result .= "\n";

									$total_refunded = 0.0;

									foreach ( $offline_change->refunded_items->children() as $refunded_item ) {

										$refunded_item = array(
											'refund_total' => $refunded_item->refund_total->__toString(),
											'refund_tax'   => $refunded_item->refund_tax->__toString(),
											'refund_taxes' => json_decode( $refunded_item->refund_taxes->__toString(), true ),
											'oipid'        => $refunded_item->order_item_product_id->__toString(),
											'qty'          => $refunded_item->quantity->__toString(),
											'restock_qty'  => $refunded_item->restock_quantity->__toString(),
											'oiid'         => $refunded_item->order_item_id->__toString(),
										);

										$total_refunded += (float) $refunded_item['refund_total'] + (float) $refunded_item['refund_tax'];

										$wc_product = wc_get_product( $refunded_item['oipid'] );

										$import_result .= "\t- " . $refunded_item['qty'] . ' x "' . $wc_product->get_title() . '", ' . wp_strip_all_tags( wc_price( (float) $refunded_item['refund_total'] + (float) $refunded_item['refund_tax'] ) ) . ', ' . $refunded_item['restock_qty'] . ' ' . esc_html( $fooeventspos_phrases['label_refund_order_restocked'] ) . "\n";

										$refunded_items[] = $refunded_item;
									}

									$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_refund_order_total_refunded'] ) . ': ' . wp_strip_all_tags( wc_price( $total_refunded ) ) . "\n";

									$refund_result = fooeventspos_do_refund_order( $order_id, $refunded_items );
									$wc_order      = $refund_result['order'];

									if ( ! empty( $refund_result['square_refund'] ) ) {

										if ( 'success' === $refund_result['square_refund'] ) {

											$import_result .= "\t\t* " . esc_html( $fooeventspos_phrases['label_refund_order_square_refunded_success'] ) . "\n";

										} else {

											$import_result .= "\t\t* " . esc_html( $fooeventspos_phrases['label_refund_order_square_refunded_fail'] ) . "\n";

										}
									}

									$success = ! empty( $wc_order );

									if ( $success ) {
										$fooeventspos_offline_changes[] = $offline_change_id;
									} else {
										$import_result = 'failed';

										$failure = true;
									}
								}
							} elseif ( 'create_update_order' === $offline_change_type || 'create_order' === $offline_change_type ) {
								$update_order = '' !== $offline_change->existing_order_id->__toString();

								$import_title = $update_order ? esc_html( $fooeventspos_phrases['label_update_order'] ) . ' #' . $offline_change->existing_order_id->__toString() : esc_html( $fooeventspos_phrases['label_create_order'] );

								if ( $already_imported ) {
									$import_result = $update_order ? esc_html( $fooeventspos_phrases['label_update_order_skipped'] ) : esc_html( $fooeventspos_phrases['label_create_order_skipped'] ) . "\n";
								} else {
									$import_result .= "\n";

									$order_items = array();

									if ( isset( $offline_change->order_items ) && ! empty( $offline_change->order_items->children() ) ) {
										foreach ( $offline_change->order_items->children() as $order_item ) {
											$order_item = array(
												'oilst' => $order_item->line_subtotal->__toString(),
												'oiq'   => $order_item->quantity->__toString(),
												'pid'   => $order_item->product_id->__toString(),
												'oiltl' => $order_item->line_total->__toString(),
											);

											$order_items[] = $order_item;

											$wc_product = wc_get_product( $order_item['pid'] );

											$product_title = $wc_product->get_title();

											if ( 'variation' === $wc_product->get_type() ) {
												$product_title .= ' (';

												$atts = $wc_product->get_attributes();

												$att_values = array();

												foreach ( $atts as $att_name => $att_value ) {
													$att_values[] = $att_value;
												}

												$product_title .= implode( ', ', $att_values ) . ')';
											}

											$import_result .= "\t- " . $order_item['oiq'] . ' x "' . $product_title . '", ' . wp_strip_all_tags( wc_price( $order_item['oiltl'] ) ) . "\n";
										}
									}

									$order_items_json = wp_json_encode( $order_items );

									$order_customer = array(
										'cid' => '',
									);

									if ( isset( $offline_change->customer ) && ! empty( $offline_change->customer->children() ) ) {
										$order_customer = array(
											'cid' => $offline_change->customer->id->__toString(),
											'cfn' => isset( $offline_change->customer->first_name ) ? trim( $offline_change->customer->first_name->__toString() ) : '',
											'cln' => isset( $offline_change->customer->last_name ) ? trim( $offline_change->customer->last_name->__toString() ) : '',
											'ce'  => isset( $offline_change->customer->email ) ? trim( $offline_change->customer->email->__toString() ) : '',
										);

										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_customer'] ) . ': ' . $order_customer['cfn'] . ' ' . $order_customer['cln'] . ' ( ' . $order_customer['cid'] . ' ) - ' . $order_customer['ce'] . "\n";

										if ( isset( $offline_change->customer->billing_address ) ) {
											$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_billing_address'] ) . "\n";

											$order_customer['cbfn'] = isset( $offline_change->customer->billing_address->first_name ) ? trim( $offline_change->customer->billing_address->first_name->__toString() ) : '';
											$order_customer['cbln'] = isset( $offline_change->customer->billing_address->last_name ) ? trim( $offline_change->customer->billing_address->last_name->__toString() ) : '';
											$order_customer['cbco'] = isset( $offline_change->customer->billing_address->company ) ? trim( $offline_change->customer->billing_address->company->__toString() ) : '';
											$order_customer['cba1'] = isset( $offline_change->customer->billing_address->address_1 ) ? trim( $offline_change->customer->billing_address->address_1->__toString() ) : '';
											$order_customer['cba2'] = isset( $offline_change->customer->billing_address->address_2 ) ? trim( $offline_change->customer->billing_address->address_2->__toString() ) : '';
											$order_customer['cbc']  = isset( $offline_change->customer->billing_address->city ) ? trim( $offline_change->customer->billing_address->city->__toString() ) : '';
											$order_customer['cbpo'] = isset( $offline_change->customer->billing_address->post_code ) ? trim( $offline_change->customer->billing_address->post_code->__toString() ) : '';
											$order_customer['cbcu'] = isset( $offline_change->customer->billing_address->country ) ? trim( $offline_change->customer->billing_address->country->__toString() ) : '';
											$order_customer['cbs']  = isset( $offline_change->customer->billing_address->state ) ? trim( $offline_change->customer->billing_address->state->__toString() ) : '';
											$order_customer['cbph'] = isset( $offline_change->customer->billing_address->phone ) ? trim( $offline_change->customer->billing_address->phone->__toString() ) : '';
											$order_customer['cbe']  = isset( $offline_change->customer->billing_address->email ) ? trim( $offline_change->customer->billing_address->email->__toString() ) : '';
										}

										if ( isset( $offline_change->customer->shipping_address ) ) {
											$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_shipping_address'] ) . "\n";

											$order_customer['csfn'] = isset( $offline_change->customer->shipping_address->first_name ) ? trim( $offline_change->customer->shipping_address->first_name->__toString() ) : '';
											$order_customer['csln'] = isset( $offline_change->customer->shipping_address->last_name ) ? trim( $offline_change->customer->shipping_address->last_name->__toString() ) : '';
											$order_customer['csco'] = isset( $offline_change->customer->shipping_address->company ) ? trim( $offline_change->customer->shipping_address->company->__toString() ) : '';
											$order_customer['csa1'] = isset( $offline_change->customer->shipping_address->address_1 ) ? trim( $offline_change->customer->shipping_address->address_1->__toString() ) : '';
											$order_customer['csa2'] = isset( $offline_change->customer->shipping_address->address_2 ) ? trim( $offline_change->customer->shipping_address->address_2->__toString() ) : '';
											$order_customer['csc']  = isset( $offline_change->customer->shipping_address->city ) ? trim( $offline_change->customer->shipping_address->city->__toString() ) : '';
											$order_customer['cspo'] = isset( $offline_change->customer->shipping_address->post_code ) ? trim( $offline_change->customer->shipping_address->post_code->__toString() ) : '';
											$order_customer['cscu'] = isset( $offline_change->customer->shipping_address->country ) ? trim( $offline_change->customer->shipping_address->country->__toString() ) : '';
											$order_customer['css']  = isset( $offline_change->customer->shipping_address->state ) ? trim( $offline_change->customer->shipping_address->state->__toString() ) : '';
											$order_customer['csph'] = isset( $offline_change->customer->shipping_address->phone ) ? trim( $offline_change->customer->shipping_address->phone->__toString() ) : '';
										}
									}

									$order_customer_json = wp_json_encode( $order_customer );

									$order_attendee_details = array();

									if ( isset( $offline_change->attendee_details ) && ! empty( $offline_change->attendee_details->children() ) ) {
										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_attendee_details'] ) . "\n";

										foreach ( $offline_change->attendee_details->children() as $attendee_detail ) {
											$element_name = substr( $attendee_detail->getName(), strlen( 'attendee_' ) );

											$order_attendee_details[ $element_name ] = $attendee_detail->__toString();
										}
									}

									$order_attendee_details_json = wp_json_encode( $order_attendee_details );

									$payments = array();

									if ( isset( $offline_change->payments ) && ! empty( $offline_change->payments->children() ) ) {
										foreach ( $offline_change->payments->children() as $payment ) {
											$payment_args = array(
												'pd'   => $payment->date->__toString(),
												'opmk' => $payment->payment_method_key->__toString(),
												'oud'  => $payment->user_id->__toString(),
												'tid'  => $payment->transaction_id->__toString(),
												'pa'   => $payment->amount->__toString(),
												'np'   => $payment->number_of_payments->__toString(),
												'pn'   => $payment->payment_number->__toString(),
												'pap'  => $payment->paid->__toString(),
												'par'  => $payment->refunded->__toString(),
											);

											$payments[] = $payment_args;
										}
									}

									$payments_json = wp_json_encode( $payments );

									$analytics = array();

									if ( isset( $offline_change->analytics ) && ! empty( $offline_change->analytics->children() ) ) {
										$analytics = array(
											'platform' => $offline_change->analytics->platform->__toString(),
											'location' => $offline_change->analytics->location->__toString(),
											'currency' => $offline_change->analytics->currency->__toString(),
										);
									}

									$analytics_json = wp_json_encode( $analytics );

									$order_params = array(
										isset( $offline_change->date ) ? $offline_change->date->__toString() : '',
										isset( $offline_change->payment_method ) ? $offline_change->payment_method->__toString() : '',
										'[]',
										$order_items_json,
										$order_customer_json,
										isset( $offline_change->order_note ) ? $offline_change->order_note->__toString() : '',
										isset( $offline_change->order_note_send_to_customer ) ? $offline_change->order_note_send_to_customer->__toString() : '',
										$order_attendee_details_json,
										isset( $offline_change->square_order_id ) ? $offline_change->square_order_id->__toString() : '',
										isset( $offline_change->user_id ) ? $offline_change->user_id->__toString() : '',
										isset( $offline_change->stripe_payment_id ) ? $offline_change->stripe_payment_id->__toString() : '',
										$analytics_json,
										isset( $offline_change->status ) ? $offline_change->status->__toString() : '',
										isset( $offline_change->existing_order_id ) ? $offline_change->existing_order_id->__toString() : '',
										$payments_json,
									);

									if ( isset( $offline_change->date ) ) {
										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_order_date'] ) . ': ' . gmdate( 'Y-m-d H:i:s', (int) $offline_change->date->__toString() ) . "\n";
									}

									if ( isset( $offline_change->payment_method ) ) {
										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_payment_method'] ) . ': "' . fooeventspos_get_payment_method_from_key( $offline_change->payment_method->__toString() ) . "\"\n";
									}

									if ( isset( $offline_change->status ) ) {
										$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_set_status'] ) . ': "' . $order_statuses[ $offline_change->status->__toString() ] . "\"\n";
									}

									$imported_order = fooeventspos_do_create_update_order( $order_params );

									$success = ! empty( $imported_order ) && false !== $imported_order;

									if ( $success ) {
										if ( $update_order ) {
											$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_update_order_updated'] ) . "\n";
										} else {
											$import_result .= "\t- " . esc_html( $fooeventspos_phrases['label_create_order_total'] ) . ': ' . wp_strip_all_tags( wc_price( $imported_order->get_total() ) ) . "\n";
										}

										$fooeventspos_offline_changes[] = $offline_change_id;
									} else {
										$import_result = 'failed';

										$failure = true;
									}
								}
							}

							printf(
								"%d/%d %s: %s\n",
								esc_html( $change_count++ ),
								esc_html( $total_changes ),
								esc_html( $import_title ),
								esc_html( $import_result )
							);
						}

						update_option( 'globalFooEventsPOSOfflineChanges', wp_json_encode( $fooeventspos_offline_changes ) );

						$import_output = ob_get_contents();

						ob_get_clean();
						?>
					<textarea id="fooeventspos_import_log" class="widefat" rows="10" readonly><?php echo esc_html( $import_output ); ?></textarea>
					<p style="text-align:right;"><button class="button button-primary" href="javascript:void(0);" id="fooeventspos_import_log_copy_button"><?php echo esc_html( $fooeventspos_phrases['button_copy_to_clipboard'] ); ?></button></p>
						<?php
						if ( $failure ) {
							echo "<div class='notice notice-error is-dismissible'><p>" . esc_html( $fooeventspos_phrases['description_problem_importing'] ) . '</p></div>';
						} else {
							echo "<div class='notice notice-success is-dismissible'><p>" . esc_html( $fooeventspos_phrases['description_all_changes_imported'] ) . '</p></div>';
						}
					} else {
						echo "<div class='notice notice-error is-dismissible'><p>" . esc_html( $fooeventspos_phrases['description_no_changes_found'] ) . '</p></div>';
					}
				}
			} else {
				echo "<div class='notice notice-error is-dismissible'><p>" . esc_html( $fooeventspos_phrases['description_invalid_file_format'] ) . '</p></div>';
			}
		}
		?>
		<hr />
		<?php
	}
	?>
	<p><?php echo esc_html( $fooeventspos_phrases['description_import_intro'] ); ?></p>
	<p><?php echo esc_html( $fooeventspos_phrases['description_import_xml'] ); ?></p>
	<form enctype="multipart/form-data" id="fooeventspos-import-upload-form" method="post" class="wp-upload-form" action="">
		<p>
			<?php
				wp_nonce_field( 'fooeventspos-import' );

				$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
				$size  = size_format( $bytes );

				printf(
					'<label for="upload">%s:</label> (%s)',
					esc_html( $fooeventspos_phrases['label_select_xml'] ),
					sprintf( esc_html( $fooeventspos_phrases['label_maximum_size'] ) . ': %s', esc_html( $size ) )
				);
				?>
			<input type="file" id="upload" name="fooeventspos-import" size="25" accept=".xml" />
		</p>
		<?php submit_button( esc_html( $fooeventspos_phrases['button_upload_import'] ), 'primary' ); ?>
	</form>
</div>
<style type="text/css">
	img#fooeventspos_importing_spinner {
		vertical-align:text-bottom;
		margin-left:1em;
	}
</style>
<script type="text/javascript">
	jQuery( 'form#fooeventspos-import-upload-form' ).submit(function(e) {
		jQuery( 'form#fooeventspos-import-upload-form input#submit' ).attr( 'value', '<?php echo esc_html( $fooeventspos_phrases['button_importing_wait'] ); ?>' ).prop( 'disabled', true).parent().append( '<img src="<?php echo esc_attr( get_admin_url() ); ?>images/loading.gif" id="fooeventspos_importing_spinner" />' );
	} );

	jQuery( 'button#fooeventspos_import_log_copy_button' ).click(function() {
		var copyButton = jQuery(this );

		copyButton.prop( 'disabled', true );

		jQuery( 'textarea#fooeventspos_import_log' ).select();

		document.execCommand( 'copy' );

		copyButton.text( '<?php echo esc_html( $fooeventspos_phrases['button_copied'] ); ?>' );

		setTimeout(function() {
			copyButton.text( '<?php echo esc_html( $fooeventspos_phrases['button_copy_to_clipboard'] ); ?>' );

			copyButton.prop( 'disabled', false );
		}, 1000 );
	} );
</script>
