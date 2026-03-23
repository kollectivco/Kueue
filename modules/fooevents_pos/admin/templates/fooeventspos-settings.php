<?php
/**
 * HTML template for all FooEvents POS settings
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once get_home_path() . '/wp-admin/includes/plugin.php';
}

if ( get_option( 'fooeventspos_flush_rewrite_rules_flag' ) ) {
	flush_rewrite_rules();
	delete_option( 'fooeventspos_flush_rewrite_rules_flag' );
}
?>
<div class="wrap" id="fooeventspos-settings-page">
	<h1 class="wp-heading-inline"><?php echo esc_html( $fooeventspos_phrases['title_fooeventspos_settings'] ); ?></h1>
	<?php require_once plugin_dir_path( __FILE__ ) . 'fooevents-plugin-inactive-notice.php'; ?>
	<?php if ( isset( $fooeventspos_status ) && 'not-found' !== $fooeventspos_status && 'general' === $active_tab ) : ?>
		<?php if ( 0 < $issue_count ) { ?>
			<?php if ( PAnD::is_admin_notice_active( 'disable-notice-fooeventspos-health-issues-7' ) ) { ?>
				<div data-dismissible="disable-notice-fooeventspos-health-issues-7" class="notice notice-error is-dismissible">
					<p><?php echo esc_html( $fooeventspos_phrases['status_notice_issues_identified'] ); ?> <a href="?page=fooeventspos-settings&tab=status"><?php echo esc_html( $fooeventspos_phrases['status_notice_link_issues_identified'] ); ?></a></p>
				</div>
			<?php } ?>
		<?php } ?>
	<?php endif; ?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=fooeventspos-settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_general'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=users" class="nav-tab <?php echo 'users' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_users'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=products" class="nav-tab <?php echo 'products' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_products'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=orders" class="nav-tab <?php echo 'orders' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_orders'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=receipts" class="nav-tab <?php echo 'receipts' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_receipts'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=payments" class="nav-tab <?php echo 'payments' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_payments'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=integration" class="nav-tab <?php echo 'integration' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_integration'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=analytics" class="nav-tab <?php echo 'analytics' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $fooeventspos_phrases['title_analytics'] ); ?></a>
		<a href="?page=fooeventspos-settings&tab=status" class="nav-tab <?php echo 'status' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php echo esc_html( $fooeventspos_phrases['title_status'] ); ?> 
			<?php if ( 0 < $issue_count ) { ?>
				<?php if ( PAnD::is_admin_notice_active( 'disable-notice-fooeventspos-health-issues-7' ) ) { ?>
					<span class="amount">
						<?php echo esc_html( $issue_count ); ?>
					</span>
				<?php } ?>
			<?php } ?>
		</a>
	</h2>
	<div id="fooeventspos_settings_container">
	<?php if ( ( 'general' !== $active_tab || isset( $fooeventspos_appearance_settings ) ) && 'status' !== $active_tab && 'payments' !== $active_tab ) : ?>
	<form method="post" action="options.php">
	<?php endif; ?>
		<table class="form-table fooeventspos-settings fooeventspos-settings-<?php echo esc_attr( $active_tab ); ?>">
			<?php if ( 'general' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-general' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-general' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<div class="fooeventspos-introduction">
							<?php require_once plugin_dir_path( __FILE__ ) . 'fooevents-pos-settings-general-getting-started.php'; ?>
							<?php require_once plugin_dir_path( __FILE__ ) . 'fooevents-pos-settings-general-license-key.php'; ?>
						</div>
					</th>
				</tr>
				<?php require_once plugin_dir_path( __FILE__ ) . 'fooevents-pos-settings-general-appearance.php'; ?>
			<?php endif; ?>

			<?php if ( 'users' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-users' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-users' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<p><?php echo esc_html( $fooeventspos_phrases['description_customer_settings'] ); ?></p>
						<h2><?php echo esc_html( $fooeventspos_phrases['title_app_customers'] ); ?></h2>
						<p><?php echo esc_html( $fooeventspos_phrases['description_app_customers'] ); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_customer_user_role'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSCustomerUserRole[]" id="globalFooEventsPOSCustomerUserRole" class="text" multiple="multiple" size="<?php echo count( $customer_user_roles_options ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $customer_user_roles_options as $customer_user_role_option => $customer_user_role_value ) {
								?>
								<option value="<?php echo esc_attr( $customer_user_role_option ); ?>" <?php echo ! empty( $customer_user_role ) && in_array( $customer_user_role_option, $customer_user_role, true ) ? 'selected' : ''; ?>><?php echo esc_html( $customer_user_role_value['name'] ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_customer_user_role_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_default_order_customer'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSDefaultCustomer" id="globalFooEventsPOSDefaultCustomer" class="text fooeventspos-customer-search">
							<option value=""><?php echo esc_html( $fooeventspos_phrases['text_guest'] ); ?> <?php echo esc_html( $fooeventspos_phrases['text_customer'] ); ?></option>
							<?php if ( '' !== $default_order_customer ) : ?>
								<option value="<?php echo esc_attr( $default_order_customer ); ?>" selected><?php echo esc_html( $default_order_customer_display ); ?></option>
							<?php endif; ?>
						</select> 
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_default_order_customer_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						<?php wp_nonce_field( 'fooeventspos_default_customer', 'fooeventspos_default_customer_nonce' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_app_users'] ); ?></h2>
						<p><?php printf( esc_html( $fooeventspos_phrases['description_app_users'] ), '<a href="https://wordpress.org/plugins/user-role-editor/" target="_blank">', '</a>' ); ?></p>
					</th>
				</tr>
			<?php endif; ?>

			<?php if ( 'products' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-products' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-products' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<p><?php echo esc_html( $fooeventspos_phrases['description_product_settings'] ); ?></p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_products_to_display'] ); ?></th>
					<td>
						<fieldset>
							<ul>
								<li><label><input type="radio" name="globalFooEventsPOSProductsToDisplay" value="all" <?php echo ( 'all' === $products_to_display || empty( $products_to_display ) ) ? 'checked' : ''; ?>> <?php echo esc_html( $fooeventspos_phrases['radio_show_all_categories'] ); ?></label></li>
								<li><label><input type="radio" name="globalFooEventsPOSProductsToDisplay" value="cat" <?php echo ( 'cat' === $products_to_display ) ? 'checked' : ''; ?>> <?php echo esc_html( $fooeventspos_phrases['radio_specific_categories'] ); ?>:</label></li>
							</ul>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th></th>
					<td>
						<select name="globalFooEventsPOSProductCategories[]" id="globalFooEventsPOSProductCategories" class="text" multiple="multiple" <?php echo ( 'all' === $products_to_display || empty( $products_to_display ) ) ? 'disabled="disabled"' : ''; ?> style="<?php echo ( count( $cat_options ) < 5 ) ? 'height:100px' : 'height:200px'; ?>">
							<?php
							foreach ( $cat_options as $category_id => $category_value ) {
								?>
								<option value="<?php echo esc_attr( $category_id ); ?>" <?php echo ! empty( $product_categories ) && in_array( (string) $category_id, $product_categories, true ) ? 'selected' : ''; ?>><?php echo esc_html( $category_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_products_to_display_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_product_status'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSProductsStatus[]" id="globalFooEventsPOSProductsStatus" class="text" multiple="multiple" size="<?php echo count( $status_options ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $status_options as $status_option => $status_value ) {
								?>
								<option value="<?php echo esc_attr( $status_option ); ?>" <?php echo ! empty( $products_status ) && in_array( $status_option, $products_status, true ) ? 'selected' : ''; ?>><?php echo esc_html( $status_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_product_status_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_products_per_page'] ); ?></th>
					<td>
						<select name="globalFooEventsPOSProductsPerPage" id="globalFooEventsPOSProductsPerPage" class="text">
							<?php
							foreach ( $products_per_page_array as $products_per_page_amount ) {
								?>
								<option <?php echo ( ( ! empty( $products_per_page ) && $products_per_page === $products_per_page_amount ) || ( empty( $products_per_page ) && '500' === $products_per_page_amount ) ) ? 'selected' : ''; ?>><?php echo esc_html( $products_per_page_amount ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_products_per_page_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_products_in_stock'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSProductsOnlyInStock" value="yes" <?php echo ( ! empty( $products_only_in_stock ) && 'yes' === $products_only_in_stock ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_show_products_in_stock_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_confirm_stock_availability'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSCheckStockAvailability" value="yes" <?php echo ( ! empty( $check_stock_availability ) && 'yes' === $check_stock_availability ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_confirm_stock_availability_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_attribute_labels'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSProductsShowAttributeLabels" value="yes" <?php echo ( ! empty( $products_show_attribute_labels ) && 'yes' === $products_show_attribute_labels ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_show_attribute_labels_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_load_product_images'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSProductsLoadImages" value="yes" <?php echo ( ! empty( $products_load_images ) && 'yes' === $products_load_images ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_load_product_images_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_use_decimals_in_quantities'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSProductsUseDecimalQuantities" value="yes" class="fooeventspos-products-use-decimal-quantities" <?php echo ( ! empty( $products_use_decimal_quantities ) && 'yes' === $products_use_decimal_quantities ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_use_decimals_in_quantities_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr id="fooeventspos_decimal_quantity_notice_row" style="<?php echo ( ! empty( $products_use_decimal_quantities ) && 'yes' === $products_use_decimal_quantities ) ? '' : 'display:none;'; ?>">
					<th scope="row" style="padding-top:0px;padding-bottom:0px;"></th>
					<td style="padding-top:0px;padding-bottom:0px;">
						<mark class="error fooeventspos-mark" style="padding:0;"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( $fooeventspos_phrases['label_important'] ); ?></mark>
						<br/>
						<?php echo esc_html( $fooeventspos_phrases['description_decimal_quantities'] ); ?>
						<br/>
						<br/>
						<?php printf( esc_html( $fooeventspos_phrases['description_decimal_quantity_default_settings'] ), '<a href="' . esc_attr( admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) ) . '">', '</a>' ); ?> <?php echo esc_html( $fooeventspos_phrases['description_decimal_quantity_product_settings'] ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( 'orders' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-orders' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-orders' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<p><?php echo esc_html( $fooeventspos_phrases['description_orders_settings'] ); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['checkbox_only_load_pos_orders'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSOnlyLoadPOSOrders" value="yes" <?php echo ( ! empty( $only_load_pos_orders ) && 'yes' === $only_load_pos_orders ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_only_load_pos_orders_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_order_statuses_to_load'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSOrderLoadStatuses[]" id="globalFooEventsPOSOrderLoadStatuses" class="text" multiple="multiple" size="<?php echo count( array_keys( $order_load_status_values ) ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $order_load_status_values as $order_status => $order_status_value ) {
								?>
								<option value="<?php echo esc_attr( $order_status ); ?>" <?php echo ! empty( $order_load_statuses ) && in_array( $order_status, $order_load_statuses, true ) ? 'selected' : ''; ?>><?php echo esc_html( $order_status_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_order_statuses_to_load_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_orders_to_load'] ); ?></th>
					<td>
						<select name="globalFooEventsPOSOrdersToLoad" id="globalFooEventsPOSOrdersToLoad" class="text">
							<?php
							foreach ( $order_limit_array as $order_limit_key => $order_limit_value ) {
								?>
								<option <?php echo ( ( ! empty( $orders_to_load ) && $orders_to_load === (string) $order_limit_key ) || ( empty( $orders_to_load ) && '100' === (string) $order_limit_key ) ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $order_limit_key ); ?>"><?php echo esc_html( $order_limit_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_orders_to_load_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_fetch_order_notes'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSFetchOrderNotes" value="yes" <?php echo ( ! empty( $fetch_order_notes ) && 'yes' === $fetch_order_notes ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_fetch_order_notes_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_order_submit_statuses'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSOrderSubmitStatuses[]" id="globalFooEventsPOSOrderSubmitStatuses" class="text" multiple="multiple" size="<?php echo count( array_keys( $order_submit_status_values ) ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $order_submit_status_values as $order_status => $order_status_value ) {
								?>
								<option value="<?php echo esc_attr( $order_status ); ?>" <?php echo ! empty( $order_submit_statuses ) && in_array( $order_status, $order_submit_statuses, true ) ? 'selected' : ''; ?>><?php echo esc_html( $order_status_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_order_submit_statuses_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_default_order_status'] ); ?></th>
					<td>
						<select name="globalFooEventsPOSDefaultOrderStatus" id="globalFooEventsPOSDefaultOrderStatus" class="text">
							<?php
							foreach ( $order_submit_status_values as $order_status => $order_status_value ) {
								?>
								<option <?php echo ( ( ! empty( $default_order_status ) && $default_order_status === (string) $order_status ) || ( empty( $default_order_status ) && 'completed' === (string) $default_order_status ) ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $order_status ); ?>"><?php echo esc_html( $order_status_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_default_order_status_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_order_incomplete_statuses'] ); ?></th>
					<td valign="top">
						<select name="globalFooEventsPOSOrderIncompleteStatuses[]" id="globalFooEventsPOSOrderIncompleteStatuses" class="text" multiple="multiple" size="<?php echo count( array_keys( $order_incomplete_status_values ) ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $order_incomplete_status_values as $order_status => $order_status_value ) {
								?>
								<option value="<?php echo esc_attr( $order_status ); ?>" <?php echo ! empty( $order_incomplete_statuses ) && in_array( $order_status, $order_incomplete_statuses, true ) ? 'selected' : ''; ?>><?php echo esc_html( $order_status_value ); ?></option>
								<?php
							}
							?>
						</select>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_order_incomplete_statuses_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_disable_new_order_emails'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSDisableNewOrderEmails" value="yes" <?php echo ( ! empty( $disable_new_order_emails ) && 'yes' === $disable_new_order_emails ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_disable_new_order_emails_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['setting_title_new_order_alerts'] ); ?></th>
					<td valign="top">
						<p>
							<?php echo esc_html( $fooeventspos_phrases['setting_text_order_status'] ); ?>
							<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_text_order_status_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</p>
						<select name="globalFooEventsPOSNewOrderAlertStatuses[]" id="globalFooEventsPOSNewOrderAlertStatuses" class="text" multiple="multiple" size="<?php echo count( array_keys( $order_load_status_values ) ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $order_load_status_values as $order_status => $order_status_value ) {
								?>
								<option value="<?php echo esc_attr( $order_status ); ?>" <?php echo ! empty( $new_order_alert_statuses ) && in_array( $order_status, $new_order_alert_statuses, true ) ? 'selected' : ''; ?>><?php echo esc_html( $order_status_value ); ?></option>
								<?php
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top"></th>
					<td valign="top">
						<p>
							<?php echo esc_html( $fooeventspos_phrases['setting_text_shipping_method'] ); ?>
							<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_text_shipping_method_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</p>
						<select name="globalFooEventsPOSNewOrderAlertShippingMethods[]" id="globalFooEventsPOSNewOrderAlertShippingMethods" class="text" multiple="multiple" size="<?php echo count( array_keys( $order_load_status_values ) ); ?>" style="overflow:hidden;">
							<?php
							foreach ( $woocommerce_shipping_methods as $shipping_method => $shipping_method_value ) {
								?>
								<option value="<?php echo esc_attr( $shipping_method ); ?>" <?php echo ! empty( $new_order_alert_shipping_methods ) && in_array( $shipping_method, $new_order_alert_shipping_methods, true ) ? 'selected' : ''; ?>><?php echo esc_html( $shipping_method_value ); ?></option>
								<?php
							}
							?>
						</select>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( 'receipts' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-receipts' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-receipts' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<p><?php echo esc_html( $fooeventspos_phrases['description_receipt_settings'] ); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_store_logo'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSStoreLogoURL" class="text uploadfield" type="text" size="40" name="globalFooEventsPOSStoreLogoURL" value="<?php echo esc_attr( $store_logo_url ); ?>" />
						<span class="uploadbox"><input class="upload_image_button_fooeventspos button" type="button" value="<?php echo esc_attr( $fooeventspos_phrases['button_upload_store_logo'] ); ?>"><a href="javascript:void(0);" class="upload_reset_fooeventspos"><?php echo esc_html( $fooeventspos_phrases['button_clear_store_logo'] ); ?></a></span>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_store_logo_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_store_name'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSStoreName" class="text" type="text" size="40" name="globalFooEventsPOSStoreName" value="<?php echo esc_attr( $store_name ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_store_name_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_header_content'] ); ?></th>
					<td valign="top">
						<textarea id="globalFooEventsPOSHeaderContent" class="text" name="globalFooEventsPOSHeaderContent"><?php echo esc_html( $header_content ); ?></textarea>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_header_content_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_receipt_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSReceiptTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_receipt_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSReceiptTitle" value="<?php echo esc_attr( $receipt_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_receipt_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_order_number_prefix'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSOrderNumberPrefix" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_order_number_prefix'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSOrderNumberPrefix" value="<?php echo esc_attr( $order_number_prefix ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_order_number_prefix_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_product_column_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSProductColumnTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_product_column_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSProductColumnTitle" value="<?php echo esc_attr( $product_column_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_product_column_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_quantity_column_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSQuantityColumnTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_quantity_column_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSQuantityColumnTitle" value="<?php echo esc_attr( $quantity_column_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_quantity_column_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_price_column_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSPriceColumnTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_price_column_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSPriceColumnTitle" value="<?php echo esc_attr( $price_column_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_price_column_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_subtotal_column_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSSubtotalColumnTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_subtotal_column_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSSubtotalColumnTitle" value="<?php echo esc_attr( $subtotal_column_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_subtotal_column_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_sku'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSShowSKU" value="yes" <?php echo ( ! empty( $show_sku ) && 'yes' === $show_sku ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_show_sku_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_guid'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSShowGUID" value="yes" <?php echo ( ! empty( $show_guid ) && 'yes' === $show_guid ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_show_guid_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_incl_abbreviation'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSInclusiveAbbreviation" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_incl_abbreviation'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSInclusiveAbbreviation" value="<?php echo esc_attr( $inclusive_abbreviation ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_incl_abbreviation_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_excl_abbreviation'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSExclusiveAbbreviation" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_excl_abbreviation'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSExclusiveAbbreviation" value="<?php echo esc_attr( $exclusive_abbreviation ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_excl_abbreviation_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_discounts_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSDiscountsTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_discounts_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSDiscountsTitle" value="<?php echo esc_attr( $discounts_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_discounts_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_refunds_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSRefundsTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_refunds_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSRefundsTitle" value="<?php echo esc_attr( $refunds_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_refunds_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_tax_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSTaxTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_tax_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSTaxTitle" value="<?php echo esc_attr( $tax_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_tax_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_total_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSTotalTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_total_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSTotalTitle" value="<?php echo esc_attr( $total_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_total_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_payment_method_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSPaymentMethodTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_payment_method_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSPaymentMethodTitle" value="<?php echo esc_attr( $payment_method_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_payment_method_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_billing_address'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSShowBillingAddress" value="yes" <?php echo ( ! empty( $show_billing_address ) && 'yes' === $show_billing_address ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_show_billing_address_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_billing_address_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSBillingAddressTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_billing_address_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSBillingAddressTitle" value="<?php echo esc_attr( $billing_address_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_billing_address_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_show_shipping_address'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSShowShippingAddress" value="yes" <?php echo ( ! empty( $show_shipping_address ) && 'yes' === $show_shipping_address ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_show_shipping_address_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_shipping_address_title'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSShippingAddressTitle" placeholder="<?php echo esc_attr( $fooeventspos_phrases['placeholder_shipping_address_title'] ); ?>" class="text" type="text" size="40" name="globalFooEventsPOSShippingAddressTitle" value="<?php echo esc_attr( $shipping_address_title ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_shipping_address_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_footer_content'] ); ?></th>
					<td valign="top">
						<textarea id="globalFooEventsPOSFooterContent" class="text" name="globalFooEventsPOSFooterContent"><?php echo esc_html( $footer_content ); ?></textarea>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_footer_content_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_fooeventspos_logo'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSReceiptShowLogo" value="yes" <?php echo ( ! empty( $receipt_show_logo ) && 'yes' === $receipt_show_logo ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_title_fooeventspos_logo_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( 'payments' === $active_tab ) : ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_payment_methods'] ); ?></h2>
						<p><?php printf( esc_html( $fooeventspos_phrases['description_pos_payment_methods'] ), '<a href="' . esc_attr( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '" target="_blank">', '</a>' ); ?></p>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_fooeventspos_pos_payments'] ); ?></h2>
						<p><?php echo esc_html( $fooeventspos_phrases['description_pos_payments'] ); ?></p>
						<p><?php printf( esc_html( $fooeventspos_phrases['text_orders_needing_update'] ), esc_html( $wc_order_count_without_payments ) ); ?></p>
						<p><a href="
						<?php
						if ( 0 === $wc_order_count_without_payments ) {
							echo 'javascript:void(0);';
						} else {
							echo '?page=fooeventspos-settings&tab=payments&update=yes';
						}
						?>
						" class="button 
						<?php
						if ( 0 === $wc_order_count_without_payments ) {
							echo 'disabled';
						}
						?>
						"><?php echo esc_html( $fooeventspos_phrases['button_update_orders'] ); ?></a></p>
						<p><a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=fooeventspos_payment' ) ); ?>"><?php echo esc_html( $fooeventspos_phrases['text_view_pos_payments'] ); ?></a></p>
					</th>
				</tr>
			<?php endif; ?>

			<?php if ( 'analytics' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-analytics' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-analytics' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_woocommerce_analytics'] ); ?></h2>
						<p><?php echo esc_html( $fooeventspos_phrases['description_woocommerce_order_analytics'] ); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['checkbox_enable_woocommerce_analytics'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSWooCommerceAnalytics" value="yes" <?php echo ( ! empty( $fooeventspos_woocommerce_analytics ) && 'yes' === $fooeventspos_woocommerce_analytics ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_enable_woocommerce_analytics_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_fooeventspos_order_analytics'] ); ?></h2>
						<p><?php echo esc_html( $fooeventspos_phrases['description_fooeventspos_order_analytics'] ); ?></p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['checkbox_enable_fooeventspos_analytics'] ); ?></th>
					<td>
						<label><input type="checkbox" name="globalFooEventsPOSAnalyticsOptIn" value="yes" <?php echo ( ! empty( $fooeventspos_analytics_opt_in ) && 'yes' === $fooeventspos_analytics_opt_in ) ? 'checked' : ''; ?>></label>
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_enable_fooeventspos_analytics_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						<?php printf( esc_html( $fooeventspos_phrases['description_privacy_policy'] ), '<a href="https://www.fooevents.com/privacy/" target="_blank">', '</a>' ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( 'integration' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-integration' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-integration' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_square_payments'] ); ?></h2>
						<p>
							<?php printf( esc_html( $fooeventspos_phrases['description_fooeventspos_square_payments'] ), '<a href="https://help.fooevents.com/docs/topics/point-of-sale/supported-hardware/#square-payments" target="_blank">', '</a>' ); ?>
							<br/>
							<?php echo esc_html( $fooeventspos_phrases['description_need_help_square_payments'] ) . ' <a href="https://help.fooevents.com/docs/topics/payments/square-payment-integration/#setup" target="_blank">' . esc_html( $fooeventspos_phrases['button_click_here'] ) . '</a>'; ?>
						</p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_square_application_id'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSSquareApplicationID" class="text" type="text" size="40" name="globalFooEventsPOSSquareApplicationID" value="<?php echo esc_attr( $square_application_id ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_square_application_id_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_square_access_token'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSSquareAccessToken" class="text" type="password" size="40" name="globalFooEventsPOSSquareAccessToken" value="<?php echo esc_attr( $square_access_token ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_square_access_token_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr id="fooeventspos_square_notice_row" style="<?php echo ( strpos( $square_application_id, 'sandbox-' ) !== false ? '' : 'display:none;' ); ?>">
					<th scope="row"></th>
					<td>
						<mark class="error fooeventspos-mark" style="padding:0;"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( $fooeventspos_phrases['label_important'] ); ?></mark>
						<div id="fooeventspos_square_sandbox" style="<?php echo ( strpos( $square_application_id, 'sandbox-' ) !== false ? '' : 'display:none;' ); ?>">
							<?php echo esc_html( $fooeventspos_phrases['description_square_sandbox_mode'] ); ?>
							<br/>
							<?php printf( esc_html( $fooeventspos_phrases['description_square_test_cards'] ), '<a href="https://developer.squareup.com/docs/devtools/sandbox/payments" target="_blank">', '</a>' ); ?>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" colspan="2">
						<h2><?php echo esc_html( $fooeventspos_phrases['title_stripe_payments'] ); ?></h2>
						<p>
							<?php printf( esc_html( $fooeventspos_phrases['description_stripe_payments'] ), '<a href="https://www.fooevents.com/features/hardware/#stripe-payments" target="_blank">', '</a>' ); ?>
							<br/>
							<?php echo esc_html( $fooeventspos_phrases['description_need_help_stripe_payments'] ) . ' <a href="https://help.fooevents.com/docs/topics/payments/stripe-payment-integration/#stripe-api-keys" target="_blank">' . esc_html( $fooeventspos_phrases['button_click_here'] ) . '</a>'; ?>
						</p>
					</th>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_stripe_publishable_key'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSStripePublishableKey" class="text fooeventspos-stripe-api-key" type="text" size="40" name="globalFooEventsPOSStripePublishableKey" value="<?php echo esc_attr( $stripe_publishable_key ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_stripe_publishable_key_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php echo esc_html( $fooeventspos_phrases['setting_title_stripe_secret_key'] ); ?></th>
					<td>
						<input id="globalFooEventsPOSStripeSecretKey" class="text fooeventspos-stripe-api-key" type="password" size="40" name="globalFooEventsPOSStripeSecretKey" value="<?php echo esc_attr( $stripe_secret_key ); ?>" />
						<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['setting_title_stripe_secret_key_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</td>
				</tr>
				<tr id="fooeventspos_stripe_notice_row" style="<?php echo ( strpos( $stripe_publishable_key, '_test_' ) !== false || strpos( $stripe_secret_key, '_test_' ) !== false || strpos( $stripe_secret_key, '...' ) !== false ) ? '' : 'display:none;'; ?>">
					<th scope="row"></th>
					<td>
						<mark class="error fooeventspos-mark" style="padding:0;"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( $fooeventspos_phrases['label_important'] ); ?></mark>
						<div id="fooeventspos_stripe_short_secret" style="<?php echo ( strpos( $stripe_secret_key, '...' ) !== false ) ? '' : 'display:none;'; ?>">
							<?php echo esc_html( $fooeventspos_phrases['description_stripe_short_key'] ); ?>
							<br/>
							<br/>
							<?php printf( esc_html( $fooeventspos_phrases['description_stripe_copy_key'] ), '<a href="https://dashboard.stripe.com/apikeys" target="_blank">', '</a>', '<a href="https://stripe.com/docs/keys" target="_blank">', '</a>' ); ?>
						</div>
						<div id="fooeventspos_stripe_test_mode" style="<?php echo ( ( strpos( $stripe_publishable_key, '_test_' ) !== false || strpos( $stripe_secret_key, '_test_' ) !== false ) && strpos( $stripe_secret_key, '...' ) === false ) ? '' : 'display:none;'; ?>">
							<?php echo esc_html( $fooeventspos_phrases['description_stripe_test_mode'] ); ?>
							<br/>
							<?php printf( esc_html( $fooeventspos_phrases['description_stripe_test_cards'] ), '<a href="https://stripe.com/docs/testing" target="_blank">', '</a>' ); ?>
						</div>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( 'status' === $active_tab ) : ?>
				<?php settings_fields( 'fooeventspos-settings-status' ); ?>
				<?php do_settings_sections( 'fooeventspos-settings-status' ); ?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<div class="fooeventspos-health">
							<p><?php echo esc_html( $fooeventspos_phrases['description_status'] ); ?></p>
							<div class="fooeventspos-health-notices"> 
								<?php foreach ( $status_outputs as $status_output ) { ?>
									<div class="fooeventspos-health-notice fooeventspos-health-notice-<?php echo esc_attr( $status_output['type'] ); ?>">
										<p>
											<?php if ( isset( $status_output['title'] ) && '' !== $status_output['title'] ) { ?>
												<strong><?php echo esc_html( $status_output['title'] ); ?>:&nbsp;</strong> 
											<?php } ?>
											<?php if ( isset( $status_output['message'] ) && '' !== $status_output['message'] ) { ?>
												<?php echo esc_html( $status_output['message'] ); ?> 
											<?php } ?>
											<?php if ( isset( $status_output['link_url'] ) && '' !== $status_output['link_url'] ) { ?>
												<a href="<?php echo esc_attr( $status_output['link_url'] ); ?>" 
												<?php if ( isset( $status_output['target'] ) && '' !== $status_output['target'] ) { ?>
													target="<?php echo esc_attr( $status_output['target'] ); ?>"
												<?php } ?>
												><?php echo esc_html( $status_output['link_label'] ); ?> &rarr;</a>
											<?php } ?>
										</p>
									</div>
								<?php } ?>
							</div>
						</div>
					</th>
				</tr>
			<?php endif; ?>
		</table>
	<?php if ( ( 'general' !== $active_tab || isset( $fooeventspos_appearance_settings ) ) && 'status' !== $active_tab && 'payments' !== $active_tab ) : ?>
		<?php submit_button(); ?>
	</form>
	<?php endif; ?>
	</div>
</div>
