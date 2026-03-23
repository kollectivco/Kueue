<?php
/**
 * Event print settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_printing" class="panel woocommerce_options_panel fooevents_options_panel">
	<h2><?php esc_attr_e( 'Stationery Builder', 'woocommerce-events' ); ?></h2>
	<div class="options_group">	
	<div id="WooCommercePrintTicketMessage"></div>
	<div class="fooevents_printing_container">
		<div class="fooevents_printing_container_inner" id="fooevents_printing_container_inner_left">
			<p class="form-field">
				<label><?php esc_attr_e( 'Format:', 'woocommerce-events' ); ?></label>
				<select name="WooCommercePrintTicketSize" id="WooCommercePrintTicketSize">
					<optgroup label="<?php esc_attr_e( 'Tickets', 'woocommerce-events' ); ?>">
						<option value="tickets_avery_letter_10"<?php echo ( 'tickets_avery_letter_10' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '10 tickets per sheet (Letter size)', 'woocommerce-events' ); ?></option>
						<option value="tickets_letter_10"<?php echo ( 'tickets_letter_10' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '10 tickets per sheet 5.5in x 1.75in (Avery 16154 Tickets Letter size)', 'woocommerce-events' ); ?></option>
						<option value="tickets_a4_10"<?php echo ( 'tickets_a4_10' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '10 tickets per sheet (A4 size)', 'woocommerce-events' ); ?></option>
						<option value="tickets_a4_3"<?php echo ( 'tickets_a4_3' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '3 tickets per sheet (A4 size)', 'woocommerce-events' ); ?></option>
						<option value="tickets_boca_55_2"<?php echo ( 'tickets_boca_55_2' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 ticket per sheet 5.5in x 2in (BOCA Printer)', 'woocommerce-events' ); ?></option>
					</optgroup>
					<br />
					<optgroup label="<?php esc_attr_e( 'Badges', 'woocommerce-events' ); ?>">
						<option value="letter_6"<?php echo ( 'letter_6' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '6 badges per sheet 4in x 3in (Avery 5392/5393 Letter size)', 'woocommerce-events' ); ?></option>
						<option value="letter_10"<?php echo ( 'letter_10' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '10 badges per sheet 4.025in x 2in (Avery 5163/8163 Letter size)', 'woocommerce-events' ); ?></option>
						<option value="a4_12" <?php echo ( 'a4_12' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '12 badges per sheet 63.5mm x 72mm (Microsoft W233 A4 size)', 'woocommerce-events' ); ?></option>
						<option value="a4_16" <?php echo ( 'a4_16' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '16 badges per sheet 99mm x 33.9mm (Microsoft W121 A4 size)', 'woocommerce-events' ); ?></option>
						<option value="a4_24" <?php echo ( 'a4_24' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '24 badges per sheet 35mm x 70mm (Microsoft W110 A4 size)', 'woocommerce-events' ); ?></option>
						<option value="letter_30" <?php echo ( 'letter_30' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '30 badges per sheet 2.625in x 1in (Avery 5160/8160 Letter size)', 'woocommerce-events' ); ?></option>
						<option value="a4_39" <?php echo ( 'a4_39' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '39 badges per sheet 66mm x 20.60mm (Microsoft W239 A4 size)', 'woocommerce-events' ); ?></option>
						<option value="a4_45" <?php echo ( 'a4_45' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '45 badges per sheet 38.5mm x 29.9mm (Microsoft W115 A4 size)', 'woocommerce-events' ); ?></option>
						<option value="dk2113_1" <?php echo ( 'dk2113_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 badge per sheet 4in x 2.5in (DK-2113 BrotherQL Label Printer)', 'woocommerce-events' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Wraparound Labels/Wristbands', 'woocommerce-events' ); ?>">
						<option value="wristband_boca_1"<?php echo ( 'wristband_boca_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 wristband per sheet 11in x 1in (BOCA Printer)', 'woocommerce-events' ); ?></option>
						<option value="letter_labels_5"<?php echo ( 'letter_labels_5' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '5 labels per sheet 9-3/4in x 1-1/4in (Avery 22845 Letter size)', 'woocommerce-events' ); ?></option>
						<option value="letter_labels_1"<?php echo ( 'letter_labels_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 Z-band Fun/Splash wristband per sheet 10in x 1in (Zebra ZD510-HC printer)', 'woocommerce-events' ); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'Certificates', 'woocommerce-events' ); ?>">
						<option value="letter_certificate_portrait_1"<?php echo ( 'letter_certificate_portrait_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 certificate per sheet (Letter size/Portrait)', 'woocommerce-events' ); ?></option>
						<option value="letter_certificate_landscape_1"<?php echo ( 'letter_certificate_landscape_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 certificate per sheet (Letter size/Landscape)', 'woocommerce-events' ); ?></option>
						<option value="a4_certificate_portrait_1"<?php echo ( 'a4_certificate_portrait_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 certificate per sheet (A4 size/Portrait)', 'woocommerce-events' ); ?></option>
						<option value="a4_certificate_landscape_1"<?php echo ( 'a4_certificate_landscape_1' === $woocommerce_print_ticket_size ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 certificate per sheet (A4 size/Landscape)', 'woocommerce-events' ); ?></option>
					</optgroup>
				</select>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The number of items to print per sheet as well as the page format.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Number of columns:', 'woocommerce-events' ); ?></label>
				<input type="number" min="1" max="4" id="WooCommercePrintTicketNrColumns" name="WooCommercePrintTicketNrColumns" value="<?php echo ( empty( $woocommerce_print_ticket_nr_columns ) ) ? '3' : esc_attr( $woocommerce_print_ticket_nr_columns ); ?>" >
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The number of columns to display in the stationery layout area. The recommended number of columns will be set by default but this can be adjusted manually.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Number of rows:', 'woocommerce-events' ); ?></label>
				<input type="number" min="1" max="4" id="WooCommercePrintTicketNrRows" name="WooCommercePrintTicketNrRows" value="<?php echo ( empty( $woocommerce_print_ticket_nr_rows ) ) ? '3' : esc_attr( $woocommerce_print_ticket_nr_rows ); ?>" >
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The number of rows to display in the stationery layout area. The recommended number of rows will be set by default but this can be adjusted manually.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">                 
				<label><?php esc_attr_e( 'Include cut lines?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsCutLinesPrintTicket" id="WooCommerceEventsCutLinesPrintTicket" <?php echo ( empty( $woocommerce_events_cut_lines_print_ticket ) || 'on' === $woocommerce_events_cut_lines_print_ticket ) ? ' checked="checked"' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display ticket cut lines on page.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />        
			</p>
			<p class="form-field">                 
				<label><?php esc_attr_e( 'Background image', 'woocommerce-events' ); ?></label>
				<input id="WooCommerceEventsTicketBackgroundImage" class="text uploadfield" type="text" size="40" name="WooCommerceEventsTicketBackgroundImage" value="<?php echo esc_attr( $woocommerce_events_ticket_background_image ); ?>" />				
				<span class="uploadbox">
					<input class="upload_image_button_woocommerce_events button" type="button" value="<?php esc_attr_e( 'Upload file', 'woocommerce-events' ); ?>" />
					<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
				</span>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Background image that will be displayed on each ticket, label or certificate page', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				<br /><br />       <br />    
			</p>
		</div>
		<div class="fooevents_printing_container_inner" id="fooevents_printing_container_inner_right">
			<p class="form-field">                 
				<label><?php esc_attr_e( 'Include all attendees', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsPrintAllTickets" id="WooCommerceEventsPrintAllTickets">
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Include all the attendees for this event in the selected stationery.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Specific ticket number(s):', 'woocommerce-events' ); ?></label>
				<input type="text" class="short" style="" name="WooCommercePrintTicketNumbers" id="WooCommercePrintTicketNumbers" value="<?php echo esc_attr( $woocommerce_print_ticket_numbers ); ?>">
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Enter the ticket number(s) that will be used to populate the selected stationery, separated by commas (,). If both the ticket number and order number fields are empty, then all the attendees for this event will be included.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Specific order number(s):', 'woocommerce-events' ); ?></label>
				<input type="text" class="short" style="" name="WooCommercePrintTicketOrders" id="WooCommercePrintTicketOrders" value="<?php echo esc_attr( $woocommerce_print_ticket_orders ); ?>">
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Enter the order number(s) that will be used to populate the selected stationery, separated by commas (,). If both the ticket number and order number fields are empty, then all the attendees for this event will be included.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Sort order:', 'woocommerce-events' ); ?></label>
				<select name="WooCommercePrintTicketSort" id="WooCommercePrintTicketSort">
					<option value="most_recent"<?php echo ( 'most_recent' === $woocommerce_print_ticket_sort ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Most recent tickets first', 'woocommerce-events' ); ?></option>
					<option value="oldest"<?php echo ( 'oldest' === $woocommerce_print_ticket_sort ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Oldest tickets first', 'woocommerce-events' ); ?></option>
					<option value="a_z1"<?php echo ( 'a_z1' === $woocommerce_print_ticket_sort ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Alphabetical by Attendee First Name', 'woocommerce-events' ); ?></option>
					<option value="a_z2"<?php echo ( 'a_z2' === $woocommerce_print_ticket_sort ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Alphabetical by Attendee Last Name', 'woocommerce-events' ); ?></option>
				</select>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Choose the sort order for how the selected stationery will be arranged when printed.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>    
		<div class="clearfix"></div>
	</div>
	<?php if ( ! empty( $post->ID ) ) : ?>

	<button type="button" class="button-primary" id="fooevents-add-printing-widgets"><?php esc_attr_e( '+ Expand Fields', 'woocommerce-events' ); ?></button>
	<div id="fooevents_printing_widgets">
		<h3>General Fields</h3>
		<div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="1">
				<span data-name="logo"><?php esc_attr_e( 'Logo/Image', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<input id="WooCommerceEventsPrintTicketLogo" class="text uploadfield" type="text" size="40" name="WooCommerceEventsPrintTicketLogo" value="" />				
					<span class="uploadbox">
						<input class="upload_image_button_woocommerce_events button" type="button" value="Upload file" />
						<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the logo or other image that you would like to display in tickets.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						<div class="clearfix"></div>
					</span>
					<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="2">
				<span data-name="custom"><?php esc_attr_e( 'Custom Text', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>	
				<div class="fooevents_printing_widget_options">
					<textarea name="WooCommerceEventsPrintTicketCustom" id="WooCommerceEventsPrintTicketCustom"></textarea>
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="3">
				<span data-name="spacer"><?php esc_attr_e( 'Empty Spacer', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<h3>Event Fields</h3>
		<div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="4">
				<span data-name="event"><?php esc_attr_e( 'Event Name Only', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>   
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="5">
				<span data-name="event_var"><?php esc_attr_e( 'Event Name/Variation', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="6">
				<span data-name="var_only"><?php esc_attr_e( 'Variation Only', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a> 
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="7">
				<span data-name="location"><?php esc_attr_e( 'Event Location', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="8">
				<span data-name="date"><?php esc_attr_e( 'Event Date', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<h3>Ticket Fields</h3>
		<div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="9">
				<span data-name="barcode"><?php esc_attr_e( 'Barcode', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="10">
				<span data-name="ticketnr"><?php esc_attr_e( 'Ticket Number', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a> 
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="11">
				<span data-name="custom_ticketnr"><?php esc_attr_e( 'Custom Ticket Number', 'woocommerce-events' ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a> 
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<h3>Attendee Fields</h3>
		<div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="12">
				<span data-name="name"><?php echo esc_html( $attendee_name_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="13">
				<span data-name="email"><?php echo esc_html( $attendee_email_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="14">
				<span data-name="phone"><?php echo esc_html( $attendee_phone_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a> 
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="15">
				<span data-name="company"><?php echo esc_html( $attendee_company_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="16">
				<span data-name="designation"><?php echo esc_html( $attendee_designation_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="17">
				<span data-name="seat"><?php echo esc_html( $attendee_seat_text ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<div class="clearfix"></div>
					<select class="fooevents_seat_label_select">
						<option value="seat_label_first"><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
						<option value="no_row_seat_label"><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
						<option value="row_label_seat_label"><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
						<option value="no_label"><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
					</select>
					<div class="clearfix"></div>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
			
			<div class="clearfix"></div>
		</div>
		<h3>Custom Attendee Fields</h3>
		<div>
			<?php
			$i = 17;
			foreach ( $cf_array as $key => $value ) :
				?>
				<div class="fooevents_printing_widget fooevents_printing_widget_init" data-order="<?php echo esc_attr( $i ); ?>">
				<span data-name="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?><span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span></span>
				<div class="fooevents_printing_widget_options">
					<select class="fooevents_printing_ticket_select">
						<option value="small"><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
						<option value="small_uppercase"><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
						<option value="medium"><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
						<option value="medium_uppercase"><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
						<option value="large"><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
						<option value="large_uppercase"><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
					</select>
					<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>  
					<div class="clearfix"></div>
				</div>
			</div>
				<?php
				++$i;
			endforeach;
			?>
			<div class="clearfix"></div>
		</div>
	</div>
</div>
<h2><?php esc_attr_e( 'Stationery layout', 'woocommerce-events' ); ?></h2>

<div class="options_group fooevents_desc">
		<p>
		<?php esc_attr_e( 'Drag the desired fields from above into the layout blocks below.', 'woocommerce-events' ); ?>			
		</p>
</div>

	<table id="fooevents_printing_layout_block" cellpadding="0" cellspacing="0" align="center">
		<tr>
			<td class="fooevents_printing_slot" id="TopLeft">
				<?php if ( ! empty( $woocommerce_badge_field_top_left ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_top_left ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_top_left, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_top_left && 'logo' !== $woocommerce_badge_field_top_left && 'spacer' !== $woocommerce_badge_field_top_left ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_top_left ) : ?>
									<textarea name="WooCommerceBadgeFieldTopLeft_custom" id="WooCommerceBadgeFieldTopLeft_custom"><?php echo esc_attr( $woocommerce_badge_field_top_left_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldTopLeft_font" id="WooCommerceBadgeFieldTopLeft_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_top_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_top_left ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldTopLeft_seat_label" id="WooCommerceBadgeFieldTopLeft_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_top_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_top_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_top_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_top_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_top_left ) : ?>
								<?php $woocommerce_badge_field_top_left_logo = ( empty( $woocommerce_badge_field_top_left_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_top_left_logo; ?>           
							<input id="WooCommerceBadgeFieldTopLeft_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldTopLeft_logo" value="<?php echo esc_attr( $woocommerce_badge_field_top_left_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_col_1" id="TopMiddle">
				<?php if ( ! empty( $woocommerce_badge_field_top_middle ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_top_middle ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_top_middle, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_top_middle && 'logo' !== $woocommerce_badge_field_top_middle && 'spacer' !== $woocommerce_badge_field_top_middle ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_top_middle ) : ?>
									<textarea name="WooCommerceBadgeFieldTopMiddle_custom" id="WooCommerceBadgeFieldTopMiddle_custom"><?php echo esc_attr( $woocommerce_badge_field_top_middle_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldTopMiddle_font" id="WooCommerceBadgeFieldTopMiddle_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_top_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_top_middle ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldTopMiddle_seat_label" id="WooCommerceBadgeFieldTopMiddle_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_top_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_top_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_top_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_top_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_top_middle ) : ?>
								<?php $woocommerce_badge_field_top_middle_logo = ( empty( $woocommerce_badge_field_top_middle_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_top_middle_logo; ?>           
							<input id="WooCommerceBadgeFieldTopMiddle_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldTopMiddle_logo" value="<?php echo esc_attr( $woocommerce_badge_field_top_middle_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_col_1 hide_col_2" id="TopRight">
				<?php if ( ! empty( $woocommerce_badge_field_top_right ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_top_right ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_top_right, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_top_right && 'logo' !== $woocommerce_badge_field_top_right && 'spacer' !== $woocommerce_badge_field_top_right ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_top_right ) : ?>
									<textarea name="WooCommerceBadgeFieldTopRight_custom" id="WooCommerceBadgeFieldTopRight_custom"><?php echo esc_attr( $woocommerce_badge_field_top_right_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldTopRight_font" id="WooCommerceBadgeFieldTopRight_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_top_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_top_right ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldTopRight_seat_label" id="WooCommerceBadgeFieldTopRight_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_top_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_top_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_top_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_top_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_top_right ) : ?>
								<?php $woocommerce_badge_field_top_right_logo = ( empty( $woocommerce_badge_field_top_right_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_top_right_logo; ?>           
							<input id="WooCommerceBadgeFieldTopRight_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldTopRight_logo" value="<?php echo esc_attr( $woocommerce_badge_field_top_right_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_col_1 hide_col_2 hide_col_3" id="_a_4">
				<?php if ( ! empty( $woocommerce_badge_field_a_4 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_a_4 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_a_4, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_a_4 && 'logo' !== $woocommerce_badge_field_a_4 && 'spacer' !== $woocommerce_badge_field_a_4 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_a_4 ) : ?>
									<textarea name="WooCommerceBadgeField_a_4_custom" id="WooCommerceBadgeField_a_4_custom"><?php echo esc_attr( $woocommerce_badge_field_a_4_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_a_4_font" id="WooCommerceBadgeField_a_4_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_a_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_a_4 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_a_4_seat_label" id="WooCommerceBadgeField_a_4_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_a_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_a_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_a_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_a_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_a_4 ) : ?>
								<?php $woocommerce_badge_field_a_4_logo = ( empty( $woocommerce_badge_field_a_4_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_a_4_logo; ?>           
							<input id="WooCommerceBadgeField_a_4_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_a_4_logo" value="<?php echo esc_attr( $woocommerce_badge_field_a_4_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>
					</div>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td class="fooevents_printing_slot hide_row_1" id="MiddleLeft">
				<?php if ( ! empty( $woocommerce_badge_field_middle_left ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_middle_left ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_middle_left, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_middle_left && 'logo' !== $woocommerce_badge_field_middle_left && 'spacer' !== $woocommerce_badge_field_middle_left ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_middle_left ) : ?>
									<textarea name="WooCommerceBadgeFieldMiddleLeft_custom" id="WooCommerceBadgeFieldMiddleLeft_custom"><?php echo esc_attr( $woocommerce_badge_field_middle_left_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldMiddleLeft_font" id="WooCommerceBadgeFieldMiddleLeft_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_middle_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_middle_left ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldMiddleLeft_seat_label" id="WooCommerceBadgeFieldMiddleLeft_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_middle_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_middle_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_middle_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_middle_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_middle_left ) : ?>
								<?php $woocommerce_badge_field_middle_left_logo = ( empty( $woocommerce_badge_field_middle_left_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_middle_left_logo; ?>           
							<input id="WooCommerceBadgeFieldMiddleLeft_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldMiddleLeft_logo" value="<?php echo esc_attr( $woocommerce_badge_field_middle_left_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_col_1" id="MiddleMiddle">
				<?php if ( ! empty( $woocommerce_badge_field_middle_middle ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_middle_middle ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_middle_middle, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_middle_middle && 'logo' !== $woocommerce_badge_field_middle_middle && 'spacer' !== $woocommerce_badge_field_middle_middle ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_middle_middle ) : ?>
									<textarea name="WooCommerceBadgeFieldMiddleMiddle_custom" id="WooCommerceBadgeFieldMiddleMiddle_custom"><?php echo esc_attr( $woocommerce_badge_field_middle_middle_custom ); ?></textarea>
								<?php endif; ?>
								<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldMiddleMiddle_font" id="WooCommerceBadgeFieldMiddleMiddle_font">
									<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
									<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
									<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
									<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
									<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
									<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_middle_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
								</select>
								<?php if ( 'seat' === $woocommerce_badge_field_middle_middle ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldMiddleMiddle_seat_label" id="WooCommerceBadgeFieldMiddleMiddle_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_middle_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_middle_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_middle_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_middle_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_middle_middle ) : ?>
								<?php $woocommerce_badge_field_middle_middle_logo = ( empty( $woocommerce_badge_field_middle_middle_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_middle_middle_logo; ?>           
							<input id="WooCommerceBadgeFieldMiddleMiddle_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldMiddleMiddle_logo" value="<?php echo esc_attr( $woocommerce_badge_field_middle_middle_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_col_1 hide_col_2" id="MiddleRight">
				<?php if ( ! empty( $woocommerce_badge_field_middle_right ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_middle_right ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_middle_right, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_middle_right && 'logo' !== $woocommerce_badge_field_middle_right && 'spacer' !== $woocommerce_badge_field_middle_right ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_middle_right ) : ?>
									<textarea name="WooCommerceBadgeFieldMiddleRight_custom" id="WooCommerceBadgeFieldMiddleRight_custom"><?php echo esc_attr( $woocommerce_badge_field_middle_right_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldMiddleRight_font" id="WooCommerceBadgeFieldMiddleRight_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_middle_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_middle_right ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldMiddleRight_seat_label" id="WooCommerceBadgeFieldMiddleRight_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_middle_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_middle_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_middle_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_middle_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_middle_right ) : ?>
								<?php $woocommerce_badge_field_middle_right_logo = ( empty( $woocommerce_badge_field_middle_right_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_middle_right_logo; ?>           
							<input id="WooCommerceBadgeFieldMiddleRight_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldMiddleRight_logo" value="<?php echo esc_attr( $woocommerce_badge_field_middle_right_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>    
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_col_1 hide_col_2 hide_col_3" id="_b_4">
				<?php if ( ! empty( $woocommerce_badge_field_b_4 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_b_4 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_b_4, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_b_4 && 'logo' !== $woocommerce_badge_field_b_4 && 'spacer' !== $woocommerce_badge_field_b_4 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_b_4 ) : ?>
									<textarea name="WooCommerceBadgeField_b_4_custom" id="WooCommerceBadgeField_b_4_custom"><?php echo esc_attr( $woocommerce_badge_field_b_4_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_b_4_font" id="WooCommerceBadgeField_b_4_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_b_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_b_4 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_b_4_seat_label" id="WooCommerceBadgeField_b_4_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_b_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_b_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_b_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_b_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_b_4 ) : ?>
								<?php $woocommerce_badge_field_b_4_logo = ( empty( $woocommerce_badge_field_b_4_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_b_4_logo; ?>           
							<input id="WooCommerceBadgeField_b_4_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_b_4_logo" value="<?php echo esc_attr( $woocommerce_badge_field_b_4_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>    
					</div>
				<?php endif; ?>
			</td>
		</tr>
		<tr>    
			<td class="fooevents_printing_slot hide_row_1 hide_row_2" id="BottomLeft">
				<?php if ( ! empty( $woocommerce_badge_field_bottom_left ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_bottom_left ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_bottom_left, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_bottom_left && 'logo' !== $woocommerce_badge_field_bottom_left && 'spacer' !== $woocommerce_badge_field_bottom_left ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_bottom_left ) : ?>
									<textarea name="WooCommerceBadgeFieldBottomLeft_custom" id="WooCommerceBadgeFieldBottomLeft_custom"><?php echo esc_attr( $woocommerce_badge_field_bottom_left_custom ); ?></textarea>
								<?php endif; ?>
								<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldBottomLeft_font" id="WooCommerceBadgeFieldBottomLeft_font">
									<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
									<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
									<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
									<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
									<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
									<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_bottom_left_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
								</select>
								<?php if ( 'seat' === $woocommerce_badge_field_bottom_left ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldBottomLeft_seat_label" id="WooCommerceBadgeFieldBottomLeft_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_bottom_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_bottom_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_bottom_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_bottom_left_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_bottom_left ) : ?>
								<?php $woocommerce_badge_field_bottom_left_logo = ( empty( $woocommerce_badge_field_bottom_left_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_bottom_left_logo; ?>           
							<input id="WooCommerceBadgeFieldBottomLeft_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldBottomLeft_logo" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_left_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div> 
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_col_1" id="BottomMiddle">
				<?php if ( ! empty( $woocommerce_badge_field_bottom_middle ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_bottom_middle ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_bottom_middle, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_bottom_middle && 'logo' !== $woocommerce_badge_field_bottom_middle && 'spacer' !== $woocommerce_badge_field_bottom_middle ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_bottom_middle ) : ?>
									<textarea name="WooCommerceBadgeFieldBottomMiddle_custom" id="WooCommerceBadgeFieldBottomMiddle_custom"><?php echo esc_attr( $woocommerce_badge_field_bottom_middle_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldBottomMiddle_font" id="WooCommerceBadgeFieldBottomMiddle_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_bottom_middle_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_bottom_middle ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldBottomMiddle_seat_label" id="WooCommerceBadgeFieldBottomMiddle_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_bottom_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_bottom_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_bottom_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_bottom_middle_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_bottom_middle ) : ?>
								<?php $woocommerce_badge_field_bottom_middle_logo = ( empty( $woocommerce_badge_field_bottom_middle_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_bottom_middle_logo; ?>           
							<input id="WooCommerceBadgeFieldBottomMiddle_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldBottomMiddle_logo" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_middle_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_col_1 hide_col_2" id="BottomRight">
				<?php if ( ! empty( $woocommerce_badge_field_bottom_right ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_bottom_right ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_bottom_right, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_bottom_right && 'logo' !== $woocommerce_badge_field_bottom_right && 'spacer' !== $woocommerce_badge_field_bottom_right ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_bottom_right ) : ?>
									<textarea name="WooCommerceBadgeFieldBottomRight_custom" id="WooCommerceBadgeFieldBottomRight_custom"><?php echo esc_attr( $woocommerce_badge_field_bottom_right_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeFieldBottomRight_font" id="WooCommerceBadgeFieldBottomRight_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_bottom_right_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_bottom_right ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeFieldBottomRight_seat_label" id="WooCommerceBadgeFieldBottomRight_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_bottom_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_bottom_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_bottom_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_bottom_right_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_bottom_right ) : ?>
								<?php $woocommerce_badge_field_bottom_right_logo = ( empty( $woocommerce_badge_field_bottom_right_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_bottom_right_logo; ?>           
							<input id="WooCommerceBadgeFieldBottomRight_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeFieldBottomRight_logo" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_right_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_col_1 hide_col_2 hide_col_3" id="_c_4">
				<?php if ( ! empty( $woocommerce_badge_field_c_4 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_c_4 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_c_4, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_c_4 && 'logo' !== $woocommerce_badge_field_c_4 && 'spacer' !== $woocommerce_badge_field_c_4 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_c_4 ) : ?>
									<textarea name="WooCommerceBadgeField_c_4_custom" id="WooCommerceBadgeField_c_4_custom"><?php echo esc_attr( $woocommerce_badge_field_c_4_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_c_4_font" id="WooCommerceBadgeField_c_4_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_c_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_c_4 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_c_4_seat_label" id="WooCommerceBadgeField_c_4_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_c_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_c_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_c_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_c_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_c_4 ) : ?>
								<?php $woocommerce_badge_field_c_4_logo = ( empty( $woocommerce_badge_field_c_4_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_c_4_logo; ?>           
							<input id="WooCommerceBadgeField_c_4_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_c_4_logo" value="<?php echo esc_attr( $woocommerce_badge_field_c_4_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
		</tr>
		<tr>    
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_row_3" id="_d_1">
				<?php if ( ! empty( $woocommerce_badge_field_d_1 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_d_1 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_d_1, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_d_1 && 'logo' !== $woocommerce_badge_field_d_1 && 'spacer' !== $woocommerce_badge_field_d_1 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_d_1 ) : ?>
									<textarea name="WooCommerceBadgeField_d_1_custom" id="WooCommerceBadgeField_d_1_custom"><?php echo esc_attr( $woocommerce_badge_field_d_1_custom ); ?></textarea>
								<?php endif; ?>
								<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_d_1_font" id="WooCommerceBadgeField_d_1_font">
									<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
									<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
									<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
									<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
									<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
									<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_d_1_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
								</select>
								<?php if ( 'seat' === $woocommerce_badge_field_d_1 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_d_1_seat_label" id="WooCommerceBadgeField_d_1_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_d_1_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_d_1_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_d_1_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_d_1_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_d_1 ) : ?>
								<?php $woocommerce_badge_field_d_1_logo = ( empty( $woocommerce_badge_field_d_1_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_d_1_logo; ?>           
							<input id="WooCommerceBadgeField_d_1_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_d_1_logo" value="<?php echo esc_attr( $woocommerce_badge_field_d_1_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div> 
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_row_3 hide_col_1" id="_d_2">
				<?php if ( ! empty( $woocommerce_badge_field_d_2 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_d_2 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_d_2, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_d_2 && 'logo' !== $woocommerce_badge_field_d_2 && 'spacer' !== $woocommerce_badge_field_d_2 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_d_2 ) : ?>
									<textarea name="WooCommerceBadgeField_d_2_custom" id="WooCommerceBadgeField_d_2_custom"><?php echo esc_attr( $woocommerce_badge_field_d_2_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_d_2_font" id="WooCommerceBadgeField_d_2_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_d_2_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_d_2 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_d_2_seat_label" id="WooCommerceBadgeField_d_2_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_d_2_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_d_2_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_d_2_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_d_2_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_d_2 ) : ?>
								<?php $woocommerce_badge_field_d_2_logo = ( empty( $woocommerce_badge_field_d_2_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_d_2_logo; ?>           
							<input id="WooCommerceBadgeField_d_2_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_d_2_logo" value="<?php echo esc_attr( $woocommerce_badge_field_d_2_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_row_3 hide_col_1 hide_col_2" id="_d_3">
				<?php if ( ! empty( $woocommerce_badge_field_d_3 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_d_3 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_d_3, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_d_3 && 'logo' !== $woocommerce_badge_field_d_3 && 'spacer' !== $woocommerce_badge_field_d_3 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_d_3 ) : ?>
									<textarea name="WooCommerceBadgeField_d_3_custom" id="WooCommerceBadgeField_d_3_custom"><?php echo esc_attr( $woocommerce_badge_field_d_3_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_d_3_font" id="WooCommerceBadgeField_d_3_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_d_3_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_d_3 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_d_3_seat_label" id="WooCommerceBadgeField_d_3_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_d_3_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_d_3_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_d_3_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_d_3_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_d_3 ) : ?>
								<?php $woocommerce_badge_field_d_3_logo = ( empty( $woocommerce_badge_field_d_3_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_d_3_logo; ?>           
							<input id="WooCommerceBadgeField_d_3_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_d_3_logo" value="<?php echo esc_attr( $woocommerce_badge_field_d_3_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
			<td class="fooevents_printing_slot hide_row_1 hide_row_2 hide_row_3 hide_col_1 hide_col_2 hide_col_3" id="_d_4">
				<?php if ( ! empty( $woocommerce_badge_field_d_4 ) ) : ?>
					<div class="fooevents_printing_widget">
						<span data-name="<?php echo esc_attr( $woocommerce_badge_field_d_4 ); ?>">
							<?php echo esc_attr( $woo_helper->widget_label( $woocommerce_badge_field_d_4, $cf_array ) ); ?>
							<span class="fooevents_printing_arrow fooevents_printing_arrow_closed"></span>
						</span>
						<div class="fooevents_printing_widget_options">
							<?php if ( 'barcode' !== $woocommerce_badge_field_d_4 && 'logo' !== $woocommerce_badge_field_d_4 && 'spacer' !== $woocommerce_badge_field_d_4 ) : ?>
								<?php if ( 'custom' === $woocommerce_badge_field_d_4 ) : ?>
									<textarea name="WooCommerceBadgeField_d_4_custom" id="WooCommerceBadgeField_d_4_custom"><?php echo esc_attr( $woocommerce_badge_field_d_4_custom ); ?></textarea>
								<?php endif; ?>
							<select class="fooevents_printing_ticket_select" name="WooCommerceBadgeField_d_4_font" id="WooCommerceBadgeField_d_4_font">
								<option value="small" <?php echo ( 'small' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small regular text', 'woocommerce-events' ); ?></option>
								<option value="small_uppercase" <?php echo ( 'small_uppercase' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Small uppercase text', 'woocommerce-events' ); ?></option>
								<option value="medium" <?php echo ( 'medium' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium regular text', 'woocommerce-events' ); ?></option>
								<option value="medium_uppercase" <?php echo ( 'medium_uppercase' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Medium uppercase text', 'woocommerce-events' ); ?></option>
								<option value="large" <?php echo ( 'large' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large regular text', 'woocommerce-events' ); ?></option>       
								<option value="large_uppercase" <?php echo ( 'large_uppercase' === $woocommerce_badge_field_d_4_font ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Large uppercase text', 'woocommerce-events' ); ?></option>       
							</select>
								<?php if ( 'seat' === $woocommerce_badge_field_d_4 ) : ?>
							<select class="fooevents_seat_label_select" name="WooCommerceBadgeField_d_4_seat_label" id="WooCommerceBadgeField_d_4_seat_label">
								<option value="seat_label_first" <?php echo ( 'seat_label_first' === $woocommerce_badge_field_d_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Seat label first, no row label e.g. Seat 1, 2', 'woocommerce-events' ); ?></option>
								<option value="no_row_seat_label" <?php echo ( 'no_row_seat_label' === $woocommerce_badge_field_d_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row name, then seat label only e.g. 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="row_label_seat_label" <?php echo ( 'row_label_seat_label' === $woocommerce_badge_field_d_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Row label and Seat label e.g. Row 1 Seat 2', 'woocommerce-events' ); ?></option>
								<option value="no_label" <?php echo ( 'no_label' === $woocommerce_badge_field_d_4_seat_label ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No labels e.g. 1, 2', 'woocommerce-events' ); ?></option>
							</select>
							<div class="clearfix"></div>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( 'logo' === $woocommerce_badge_field_d_4 ) : ?>
								<?php $woocommerce_badge_field_d_4_logo = ( empty( $woocommerce_badge_field_d_4_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_badge_field_d_4_logo; ?>           
							<input id="WooCommerceBadgeField_d_4_logo" class="text uploadfield" type="text" size="40" name="WooCommerceBadgeField_d_4_logo" value="<?php echo esc_attr( $woocommerce_badge_field_d_4_logo ); ?>" />				
							<span class="uploadbox">
								<input class="upload_image_button_woocommerce_events button" type="button" value="Choose file" />
								<div class="clearfix"></div>
							</span>
							<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a><span> | </span>
							<?php endif; ?>
							<a href="javascript:void(0);" class="fooevents_printing_widget_remove" name="fooevents_printing_widget_remove">Delete</a>
							<div class="clearfix"></div>
						</div>     
					</div>
				<?php endif; ?>
			</td>
		</tr>
	</table>

	<input type="hidden" name="WooCommerceBadgeFieldTopLeft" id="WooCommerceBadgeFieldTopLeft" value="<?php echo esc_attr( $woocommerce_badge_field_top_left ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldTopMiddle" id="WooCommerceBadgeFieldTopMiddle" value="<?php echo esc_attr( $woocommerce_badge_field_top_middle ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldTopRight" id="WooCommerceBadgeFieldTopRight" value="<?php echo esc_attr( $woocommerce_badge_field_top_right ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_a_4" id="WooCommerceBadgeField_a_4" value="<?php echo esc_attr( $woocommerce_badge_field_a_4 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldMiddleLeft" id="WooCommerceBadgeFieldMiddleLeft" value="<?php echo esc_attr( $woocommerce_badge_field_middle_left ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldMiddleMiddle" id="WooCommerceBadgeFieldMiddleMiddle" value="<?php echo esc_attr( $woocommerce_badge_field_middle_middle ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldMiddleRight" id="WooCommerceBadgeFieldMiddleRight" value="<?php echo esc_attr( $woocommerce_badge_field_middle_right ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_b_4" id="WooCommerceBadgeField_b_4" value="<?php echo esc_attr( $woocommerce_badge_field_b_4 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldBottomLeft" id="WooCommerceBadgeFieldBottomLeft" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_left ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldBottomMiddle" id="WooCommerceBadgeFieldBottomMiddle" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_middle ); ?>" />
	<input type="hidden" name="WooCommerceBadgeFieldBottomRight" id="WooCommerceBadgeFieldBottomRight" value="<?php echo esc_attr( $woocommerce_badge_field_bottom_right ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_c_4" id="WooCommerceBadgeField_c_4" value="<?php echo esc_attr( $woocommerce_badge_field_c_4 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_d_1" id="WooCommerceBadgeField_d_1" value="<?php echo esc_attr( $woocommerce_badge_field_d_1 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_d_2" id="WooCommerceBadgeField_d_2" value="<?php echo esc_attr( $woocommerce_badge_field_d_2 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_d_3" id="WooCommerceBadgeField_d_3" value="<?php echo esc_attr( $woocommerce_badge_field_d_3 ); ?>" />
	<input type="hidden" name="WooCommerceBadgeField_d_4" id="WooCommerceBadgeField_d_4" value="<?php echo esc_attr( $woocommerce_badge_field_d_4 ); ?>" />
	<br /><br />  
	<input type="button" id="fooevents_printing_save" class='button button-primary' value='<?php esc_attr_e( 'Save Changes', 'woocommerce-events' ); ?>' />
	<a href="<?php echo esc_attr( site_url() ); ?>/wp-admin/admin-ajax.php?action=woocommerce_events_attendee_badges&attendee_show=tickets&event=<?php echo esc_attr( $post->ID ); ?>" id="fooevents_printing_print" class="button" target="_BLANK"><?php esc_attr_e( 'Print Items', 'woocommerce-events' ); ?></a>
	<br /><br /><br />  
	<?php endif; ?>
</div>
