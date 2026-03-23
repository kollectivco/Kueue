<?php
/**
 * Ticket themes viewer template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config = new FooEvents_Config();
if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

	require_once ABSPATH . '/wp-admin/includes/plugin.php';

}
?>
<div id="fooevents-help-wrap">

<?php $this->display_settings_page_notices(); ?>

<div class="clear"></div> 

<div class='fooevents-help'>

<div class='fooevents-help-intro'>

	<a href="https://www.fooevents.com/" target="_blank"><img src="<?php echo esc_url( plugins_url( '../images/fooevents-icon.png', __FILE__ ) ); ?>" width="120px" alt="<?php esc_attr_e( 'FooEvents', 'woocommerce-events' ); ?>" /></a>
	<h1><?php esc_attr_e( 'Welcome to FooEvents for WooCommerce!', 'woocommerce-events' ); ?></h1>
	<p> 
		<a href="https://www.fooevents.com/" target="_blank"><?php esc_attr_e( 'FooEvents.com', 'woocommerce-events' ); ?></a> | 
		<a href="https://help.fooevents.com/" target="_blank"><?php esc_attr_e( 'Help Center', 'woocommerce-events' ); ?></a> 
	</p>
	<p class="fooevents-extensions-section-intro"><?php esc_attr_e( 'FooEvents is the #1 event, ticketing and bookings platform for WooCommerce. Sell unlimited WooCommerce tickets and bookings from your own website and manage free registration for physical events, virtual events or both (hybrid events). No commission OR ticket fees.', 'woocommerce-events' ); ?></p>

</div>

<div class="clear"></div> 

<div class="fooevents-extensions-steps">
	<div class="fooevents-extensions-step">
		<div class="fooevents-extensions-step-inner">
			<h3><?php esc_attr_e( 'Global Settings', 'woocommerce-events' ); ?></h3>
			<p><?php esc_attr_e( 'Enter your FooEvents License key and ensure your FooEvents plugins remain up to date. Configure the behavior of your events, tickets, calendar, Zoom and Mailchimp integrations as well as the FooEvents Check-ins apps.', 'woocommerce-events' ); ?></p>
			<p><a href="admin.php?page=fooevents-settings" class="button button-primary"><?php esc_attr_e( 'Global Settings', 'woocommerce-events' ); ?></a> &nbsp; <a href="https://help.fooevents.com/docs/topics/events/global-settings/"><?php esc_attr_e( 'Help Guide', 'woocommerce-events' ); ?></a></p> 
			
		</div>
	</div>
	<div class="fooevents-extensions-step">		
		<div class="fooevents-extensions-step-inner">
			<h3><?php esc_attr_e( 'Events', 'woocommerce-events' ); ?></h3>
			<p><?php esc_attr_e( 'FooEvents extends standard WooCommerce products and adds event and ticketing functionality. To create your first event, add a new product and complete the standard product fields and FooEvents option in the Product data tabs.', 'woocommerce-events' ); ?></p>
			<p><a href="post-new.php?post_type=product" class="button button-primary"><?php esc_attr_e( 'Add an Event', 'woocommerce-events' ); ?></a> &nbsp; <a href="https://help.fooevents.com/docs/topics/events/event-setup/"><?php esc_attr_e( 'Help Guide', 'woocommerce-events' ); ?></a></p>
		</div>
	</div>
	<div class="fooevents-extensions-step">		
		<div class="fooevents-extensions-step-inner fooevents-extensions-step-last">
			<h3><?php esc_attr_e( 'Tickets', 'woocommerce-events' ); ?></h3>
			<p><?php esc_attr_e( 'Tickets are generated when an order is completed which occurs automatically when the payment has been completed.', 'woocommerce-events' ); ?></p>
			<ul>
				<li><a href="edit.php?post_type=event_magic_tickets"><?php esc_attr_e( 'View Tickets', 'woocommerce-events' ); ?></a></li> 
				<li><a href="admin.php?page=fooevents-reports"><?php esc_attr_e( 'Reports', 'woocommerce-events' ); ?></a></li> 
				<li><a href="admin.php?page=fooevents-import-tickets"><?php esc_attr_e( 'Import Tickets', 'woocommerce-events' ); ?></a></li>
				<li><a href="https://www.fooevents.com/products/ticket-themes/"><?php esc_attr_e( 'Ticket Themes', 'woocommerce-events' ); ?></a></li> 
			</ul> 
		</div>
	</div>
	<div class="clear"></div> 

</div>

<div class="fooevents-help-content">

	<div class="fooevents-extensions-section fooevents-extensions-center fooevents-extensions-section-apps">
		<h2 id="fooevents-apps"><?php esc_attr_e( 'Download the FREE FooEvents Check-ins app', 'woocommerce-events' ); ?></h2>
		<p class="fooevents-extensions-section-intro"><?php esc_attr_e( 'The FooEvents Check-ins app connects securely to any WordPress website that uses FooEvents, the #1 ticket plugin for WooCommerce. This app makes it easier than ever to manage access to your events, venues and other services like a pro!', 'woocommerce-events' ); ?></p>
		<a href="https://www.fooevents.com/apps/"><img src="<?php echo esc_url( plugins_url( '../images/fooevents-apps.png', __FILE__ ) ); ?>" class="fooevents-app" /></a><br />
		<a href="https://itunes.apple.com/app/event-check-ins/id1129740503"><img src="<?php echo esc_url( plugins_url( '../images/fooevents-apps-qr-ios.png', __FILE__ ) ); ?>" title="FooEvents Check-ins apps for iOS" class="fooevents-qrcode" /></a>
		<a href="https://play.google.com/store/apps/details?id=com.fooevents.EventCheckins"><img src="<?php echo esc_url( plugins_url( '../images/fooevents-apps-qr-android.png', __FILE__ ) ); ?>" title="FooEvents Check-ins apps for Android" class="fooevents-qrcode" /></a>
		<p><em><?php esc_attr_e( 'Scan the QR codes to install the apps.', 'woocommerce-events' ); ?></em></p>
		<div class="clear"></div> 
	</div>

	<div class="clear"></div> 

	<div class="fooevents-extensions-section fooevents-extensions-center">
		<h2><?php esc_attr_e( 'FooEvents Extensions', 'woocommerce-events' ); ?></h2>
		<p class="fooevents-extensions-section-intro"><?php esc_attr_e( 'The following extensions add various advanced features to the FooEvents for WooCommerce plugin. They can be purchased separately or as part of our popular', 'woocommerce-events' ); ?> <a href="https://www.fooevents.com/pricing/" target="_blank"><?php esc_attr_e( 'bundles', 'woocommerce-events' ); ?></a>. <?php esc_attr_e( 'If you would like to upgrade to a bundle, please', 'woocommerce-events' ); ?> <a href="https://help.fooevents.com/contact/" target="_blank"><?php esc_attr_e( 'contact us', 'woocommerce-events' ); ?></a> <?php esc_attr_e( 'and we will gladly assist', 'woocommerce-events' ); ?>.</p>
		<div class="clear"></div> 
	</div>


	<div class="fooevents-extensions">
		<div class="fooevents-extension">
			<h3 id="fooevents-extensions"><a href="https://www.fooevents.com/products/fooevents-for-woocommerce/" target="_blank"><?php esc_attr_e( 'FooEvents for WooCommerce', 'woocommerce-events' ); ?></a></h3>
			<p><?php esc_attr_e( 'FooEvents adds powerful event, ticketing and booking functionality to your WooCommerce website with no commission or ticket fees.', 'woocommerce-events' ); ?></p>
			<div class="fooevents-extension-options">				
				<span class='install-status installed'><?php echo esc_attr_e( 'Installed', 'woocommerce-events' ); ?></span> | 
				<a href="https://www.fooevents.com/products/fooevents-for-woocommerce/"><?php esc_attr_e( 'Plugin details', 'woocommerce-events' ); ?></a> | <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-for-woocommerce/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
			</div>
			<div class="clear"></div>  
		</div>	

		<?php
		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {
			$installed = true;
		} else {
			$installed = false; }
		?>
	<?php
	if ( is_plugin_active( 'fooevents_pos/fooevents-pos.php' ) || is_plugin_active_for_network( 'fooevents_pos/fooevents-pos.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">    
		<h3><a href="https://www.fooevents.com/products/fooevents-pos/" target="_blank"><?php esc_attr_e( 'FooEvents POS', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'FooEvents POS (point of sale) is a web-based point of sale plugin and enables you to sell products, bookings, event tickets and print tickets in-person.', 'woocommerce-events' ); ?></p>
			
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_pos/fooevents-pos.php' ) || is_plugin_active_for_network( 'fooevents_pos/fooevents-pos.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pos/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_pos/fooevents-pos.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pos/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pos/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-pos/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>   
	</div>
	<div class="clear"></div> 

	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">    
		<h3><a href="https://www.fooevents.com/products/fooevents-bookings/" target="_blank"><?php esc_attr_e( 'FooEvents Bookings', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Offer bookings for both physical and virtual events, venues, classes and services. Let your customers check availability and book a space or slot.', 'woocommerce-events' ); ?></p>
			
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-bookings/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_bookings/fooevents-bookings.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-bookings/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-bookings/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-bookings/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>   
	</div>
	<?php
	if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">    
		<h3><a href="https://www.fooevents.com/products/fooevents-seating/" target="_blank"><?php esc_attr_e( 'FooEvents Seating', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Manage seating arrangements using our flexible seating chart builder and let attendees select their seats based on the layout of your venue.', 'woocommerce-events' ); ?></p>
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-seating/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_seating/fooevents-seating.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-seating/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-seating/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-seating/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>        
	</div>
	<div class="clear"></div> 

	<?php
	if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">    
		<h3><a href="https://www.fooevents.com/products/fooevents-custom-attendee-fields/" target="_blank"><?php esc_attr_e( 'FooEvents Custom Attendee Fields', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Capture customized attendee fields at checkout so you can tailor FooEvents according to your unique event requirements.', 'woocommerce-events' ); ?></p>
			
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-custom-attendee-fields/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-custom-attendee-fields/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-custom-attendee-fields/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-custom-attendee-fields/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>   
	</div>

	<?php
	if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">
		<h3><a href="https://www.fooevents.com/products/fooevents-pdf-tickets/" target="_blank"><?php esc_attr_e( 'FooEvents PDF Tickets', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Attach event tickets or booking confirmations as PDF files to the email that is sent to the attendee or ticket purchaser.', 'woocommerce-events' ); ?></p>
		
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pdf-tickets/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pdf-tickets/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-pdf-tickets/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-pdf-tickets/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>   

	</div>
	<div class="clear"></div> 
	<?php
	if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">    
		<h3><a href="https://www.fooevents.com/products/fooevents-multi-day/" target="_blank"><?php esc_attr_e( 'FooEvents Multi-day', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Sell tickets to events that run over multiple calendar or sequential days and perform separate check-ins for each day of the event.', 'woocommerce-events' ); ?></p>
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-multi-day/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_multi_day/fooevents-multi-day.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-multi-day/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-multi-day/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-multi-day/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>        
	</div>

	<?php
	if ( is_plugin_active( 'fooevents_express_check_in/fooevents-express-check_in.php' ) || is_plugin_active_for_network( 'fooevents_express_check_in/fooevents-express-check_in.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">
		<h3><a href="https://www.fooevents.com/products/fooevents-express-check-in/" target="_blank"><?php esc_attr_e( 'FooEvents Express Check-ins', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Ensure fast and effortless attendee check-ins at your event. Search for attendees or connect a barcode scanner to scan tickets instead of typing.', 'woocommerce-events' ); ?></p>
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents_express_check_in/fooevents-express-check_in.php' ) || is_plugin_active_for_network( 'fooevents_express_check_in/fooevents-express-check_in.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-express-check-in/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents_express_check_in/fooevents-express-check_in.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-express-check-in/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-express-check-in/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-express-check-in/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>  

	</div>
	<div class="clear"></div>	
	<?php
	if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {
		$installed = true;
	} else {
		$installed = false; }
	?>
	<div class="fooevents-extension 
	<?php
	if ( false === $installed ) {
		echo 'not-installed'; }
	?>
	">
		<h3><a href="https://www.fooevents.com/products/fooevents-calendar/" target="_blank"><?php esc_attr_e( 'FooEvents Calendar', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Display your events in a stylish calendar on your WordPress website using simple short codes and widgets.', 'woocommerce-events' ); ?></p>
		<div class="fooevents-extension-options">	
			<?php
			if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {
				echo "<span class='install-status installed'>" . esc_attr__( 'Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-calendar/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} elseif ( file_exists( ABSPATH . 'wp-content/plugins/fooevents-calendar/fooevents-calendar.php' ) ) {
					echo '<span class="install-status notinstalled">' . esc_attr__( 'Deactivated', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-calendar/' target='new'>" . esc_attr__( 'Plugin details', 'woocommerce-events' ) . '</a>';
			} else {
				echo "<span class='install-status notinstalled'>" . esc_attr__( 'Not Installed', 'woocommerce-events' ) . "</span> | <a href='https://www.fooevents.com/products/fooevents-calendar/' target='new'>" . esc_attr__( 'Get this plugin', 'woocommerce-events' ) . '</a>';
			}
			?>
			| <a href="https://help.fooevents.com/docs/topics/fooevents-plugins/fooevents-calendar/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div> 
	</div>

	<div class="fooevents-extension">
		<h3><a href="https://www.fooevents.com/products/ticket-themes/" target="_blank"><?php esc_attr_e( 'FooEvents Ticket Themes (FREE)', 'woocommerce-events' ); ?></a></h3>
		<p><?php esc_attr_e( 'Transform the appearance of your HTML and PDF tickets and make your event stand out with our FREE Ticket Themes.', 'woocommerce-events' ); ?></p>
		<div class="fooevents-extension-options">	
			<a href="https://www.fooevents.com/products/ticket-themes/"><?php esc_attr_e( 'Download', 'woocommerce-events' ); ?></a> | <a href="https://help.fooevents.com/docs/topics/tickets/ticket-themes/"><?php esc_attr_e( 'Documentation', 'woocommerce-events' ); ?></a> 
		</div>
		<div class="clear"></div>  
	</div>			
	<div class="clear"></div> 
	</div>
</div>
</div>
		</div>
