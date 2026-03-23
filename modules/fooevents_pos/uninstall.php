<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

// If uninstall not called from WordPress, then exit.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Delete settings.
delete_option( 'globalFooEventsPOSCustomerUserRole' );
delete_option( 'globalFooEventsPOSDefaultCustomer' );

delete_option( 'globalFooEventsPOSProductsToDisplay' );
delete_option( 'globalFooEventsPOSProductCategories' );
delete_option( 'globalFooEventsPOSProductsStatus' );
delete_option( 'globalFooEventsPOSProductsOnlyInStock' );
delete_option( 'globalFooEventsPOSCheckStockAvailability' );
delete_option( 'globalFooEventsPOSProductsPerPage' );
delete_option( 'globalFooEventsPOSProductsShowAttributeLabels' );
delete_option( 'globalFooEventsPOSProductsLoadImages' );
delete_option( 'globalFooEventsPOSProductsUseDecimalQuantities' );

delete_option( 'globalFooEventsPOSOnlyLoadPOSOrders' );
delete_option( 'globalFooEventsPOSOrderLoadStatuses' );
delete_option( 'globalFooEventsPOSOrdersToLoad' );
delete_option( 'globalFooEventsPOSFetchOrderNotes' );
delete_option( 'globalFooEventsPOSOrderSubmitStatuses' );
delete_option( 'globalFooEventsPOSDefaultOrderStatus' );
delete_option( 'globalFooEventsPOSOrderIncompleteStatuses' );
delete_option( 'globalFooEventsPOSDisableNewOrderEmails' );
delete_option( 'globalFooEventsPOSNewOrderAlertStatuses' );
delete_option( 'globalFooEventsPOSNewOrderAlertShippingMethods' );

delete_option( 'globalFooEventsPOSStoreLogoURL' );
delete_option( 'globalFooEventsPOSStoreName' );
delete_option( 'globalFooEventsPOSHeaderContent' );
delete_option( 'globalFooEventsPOSReceiptTitle' );
delete_option( 'globalFooEventsPOSOrderNumberPrefix' );
delete_option( 'globalFooEventsPOSProductColumnTitle' );
delete_option( 'globalFooEventsPOSQuantityColumnTitle' );
delete_option( 'globalFooEventsPOSPriceColumnTitle' );
delete_option( 'globalFooEventsPOSSubtotalColumnTitle' );
delete_option( 'globalFooEventsPOSShowSKU' );
delete_option( 'globalFooEventsPOSShowGUID' );
delete_option( 'globalFooEventsPOSInclusiveAbbreviation' );
delete_option( 'globalFooEventsPOSExclusiveAbbreviation' );
delete_option( 'globalFooEventsPOSDiscountsTitle' );
delete_option( 'globalFooEventsPOSRefundsTitle' );
delete_option( 'globalFooEventsPOSTaxTitle' );
delete_option( 'globalFooEventsPOSTotalTitle' );
delete_option( 'globalFooEventsPOSPaymentMethodTitle' );
delete_option( 'globalFooEventsPOSShowBillingAddress' );
delete_option( 'globalFooEventsPOSBillingAddressTitle' );
delete_option( 'globalFooEventsPOSShowShippingAddress' );
delete_option( 'globalFooEventsPOSShippingAddressTitle' );
delete_option( 'globalFooEventsPOSFooterContent' );
delete_option( 'globalFooEventsPOSReceiptShowLogo' );

delete_option( 'globalFooEventsPOSSquareApplicationID' );
delete_option( 'globalFooEventsPOSSquareAccessToken' );
delete_option( 'globalFooEventsPOSStripePublishableKey' );
delete_option( 'globalFooEventsPOSStripeSecretKey' );

delete_option( 'globalFooEventsPOSWooCommerceAnalytics' );
delete_option( 'globalFooEventsPOSAnalyticsOptIn' );

delete_option( 'globalFooEventsPOSSalt' );

// Delete custom tables.
$delete_tables = array(
	'square_devices',
	'square_checkouts',
	'square_refunds',
);

global $wpdb;

foreach ( $delete_tables as $table ) {
	delete_option( "fooeventspos_db_{$table}" );

	$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %1$s', $wpdb->prefix . 'fooeventspos_' . $table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
}

$wp_upload_dir           = wp_upload_dir();
$fooeventspos_upload_path = $wp_upload_dir['basedir'] . '/fooeventspos/';

WP_Filesystem();

global $wp_filesystem;

$wp_filesystem->delete( $fooeventspos_upload_path, true );
