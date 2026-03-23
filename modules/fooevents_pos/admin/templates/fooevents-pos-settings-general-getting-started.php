<?php
/**
 * HTML template for the Getting Started section for the FooEvents POS plugin
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="fooeventspos-connect">
	<h1><?php echo esc_html( $fooeventspos_phrases['title_welcome_fooevents_pos'] ); ?></h1>
	<p>
		<?php printf( esc_html( $fooeventspos_phrases['description_fooevents_pos_getting_started'] ), '<a href="https://help.fooevents.com/docs/topics/" target="_blank">', '</a>', '<a href="https://help.fooevents.com/contact/" target="_blank">', '</a>' ); ?>
	</p>
	<?php require_once plugin_dir_path( __FILE__ ) . 'fooeventspos-settings-general-launch-app.php'; ?>
</div>