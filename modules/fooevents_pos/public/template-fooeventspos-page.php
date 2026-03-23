<?php
/**
 * FooEvents POS page template (for React app)
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/public
 */

defined( 'ABSPATH' ) || exit;

?>
<?php
	require plugin_dir_path( __DIR__ ) . 'admin/helpers/fooeventspos-phrases-helper.php';
	$fooeventspos_pos_page_title = $fooeventspos_phrases['title_fooevents_pos'];
	$fooeventspos_pos_page_title = FooEvents_POS_Integration::fooeventspos_get_app_title();

	$fooeventspos_primary_color = '#b4458d';
	$fooeventspos_primary_color = FooEvents_POS_Integration::fooeventspos_get_app_primary_color();

	$wp_upload_dir          = wp_upload_dir();
	$fooeventspos_upload_url = $wp_upload_dir['baseurl'] . '/fooeventspos/';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( $fooeventspos_upload_url . 'apple-touch-icon.png' ); ?>" />
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( $fooeventspos_upload_url . 'favicon-32x32.png' ); ?>" />
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( $fooeventspos_upload_url . 'favicon-16x16.png' ); ?>" />
		<link rel="manifest" href="<?php echo esc_url( $fooeventspos_upload_url . 'fooeventspos.webmanifest' ); ?>" />
		<meta name="msapplication-TileColor" content="#ffffff" />
		<meta name="theme-color" content="<?php echo esc_attr( $fooeventspos_primary_color ); ?>" />
		<title><?php echo esc_html( $fooeventspos_pos_page_title ); ?></title>
		<?php wp_head(); ?>
		<script id="fooeventspos_wp_url" type="text/javascript">
			localStorage.setItem("WORDPRESS_URL", "<?php echo esc_url( get_rest_url() ); ?>");
			localStorage.setItem("WORDPRESS_SITE_URL", "<?php echo esc_url( get_site_url() ); ?>");
			localStorage.setItem("X-WP-Nonce", "<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>");
			localStorage.setItem("WORDPRESS_PLUGIN_VERSION", "<?php echo esc_attr( apply_filters( 'fooeventspos_current_plugin_version', '' ) ); ?>");
			localStorage.setItem("PLUGIN_BUILD", "<?php echo 'fooeventspos'; ?>");
		</script>
	</head>
	<body>
		<div id="root"></div>
		<?php wp_footer(); ?>
	</body>
</html>
