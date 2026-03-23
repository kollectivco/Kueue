<?php
/**
 * Google Map event tab template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php esc_attr_e( 'Description', 'woocommerce-events' ); ?></h2>
<p><?php echo $event_content; ?></p>
<?php if ( ( 'API' === $global_woocommerce_events_google_maps_method || empty( $global_woocommerce_events_google_maps_method ) ) && ! empty( $woocommerce_events_google_maps ) && ! empty( $global_woocommerce_events_google_maps_api_key ) ) : ?>
	<div id="google-map-holder" style="width: 100%; height: 400px;"></div>
	<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( $global_woocommerce_events_google_maps_api_key ); ?>&v=3.exp&callback=Function.prototype"></script>
	<script>
	function initialize_fooevents_google_map() {
	var mapOptions = {
	zoom: 14,
	center: new google.maps.LatLng(<?php echo esc_attr( $woocommerce_events_google_maps ); ?>),
	scrollwheel: false, 
	mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	var map = new google.maps.Map(document.getElementById('google-map-holder'),
									mapOptions);

	var image = '<?php echo esc_attr( plugins_url() ); ?>/fooevents/images/pin.png';
	var myLatLng = new google.maps.LatLng(<?php echo esc_attr( $woocommerce_events_google_maps ); ?>);
	var beachMarker = new google.maps.Marker({
	position: myLatLng,
	map: map,
	icon: image
	});

	}
	window.addEventListener("load", initialize_fooevents_google_map);
	</script>
<?php elseif ( ! empty( $woocommerce_events_google_maps ) ) : ?>
	<iframe
		width="100%"
		height="400"
		style="border:0"
		loading="lazy"
		allowfullscreen
		referrerpolicy="no-referrer-when-downgrade"
		src="https://www.google.com/maps?q=<?php echo esc_attr( $woocommerce_events_google_maps ); ?>&z=15&output=embed">
	</iframe>
<?php endif; ?>
