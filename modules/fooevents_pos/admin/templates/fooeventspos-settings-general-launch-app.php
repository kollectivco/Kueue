<?php
/**
 * HTML template for launching the FooEvents POS app
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>

<a href="<?php echo esc_url( home_url( '/' . $pos_page_slug . '/' ) ); ?>" target="_blank" class="button button-primary button-hero"><?php echo esc_html( $fooeventspos_phrases['button_launch_point_of_sale'] ); ?>&nbsp;→</a>
