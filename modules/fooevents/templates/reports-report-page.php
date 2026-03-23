<?php
/**
 * Reports report template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap" id="WooCommerceEventsReport">
	<h2><?php echo esc_attr( $event->post_title ); ?></h2>
	<p> 
		<a href="<?php echo esc_attr( get_admin_url() ); ?>admin.php?page=fooevents-reports"><?php echo esc_attr__( 'Back to Reports', 'woocommerce-events' ); ?></a> | 
		<a href="<?php echo esc_attr( get_admin_url() ); ?>post.php?post=<?php echo esc_attr( $id ); ?>&action=edit"><?php echo esc_attr__( 'Event Settings', 'woocommerce-events' ); ?></a> | 
		<a href="<?php echo esc_attr( get_admin_url() ); ?>edit.php?post_type=event_magic_tickets&event_id=<?php echo esc_attr( $id ); ?>"><?php echo esc_attr__( 'View Tickets', 'woocommerce-events' ); ?></a>        
	</p>
	<div class="options_group">
		<form method="POST" action="">
			<div class="form-field">
				<label><?php esc_attr_e( 'From', 'woocommerce-events' ); ?></label>
				<input type="text" class="WooCommerceEventsDate" id="WooCommerceEventsDateFrom" name="WooCommerceEventsDateFrom" value="<?php echo esc_attr( $previous_date ); ?>"/>
			</div>
			<div class="form-field">
				<label><?php esc_attr_e( 'To', 'woocommerce-events' ); ?></label>
				<input type="text" class="WooCommerceEventsDate" id="WooCommerceEventsDateTo" name="WooCommerceEventsDateTo" value="<?php echo esc_attr( $todays_date ); ?>"/>
			</div>
			<input type="hidden" name="eventID" id="eventID" value="<?php echo esc_attr( $id ); ?>" />
			<div class="form-submit">
				<a href="#" name="<?php echo esc_attr( $previous_month ); ?>" class="first"><?php echo esc_attr__( '30 Days', 'woocommerce-events' ); ?></a>
				<a href="#" name="<?php echo esc_attr( $previous_90_days ); ?>"><?php echo esc_attr__( '90 Days', 'woocommerce-events' ); ?></a>  
				<a href="#" name="<?php echo esc_attr( $previous_year ); ?>" class="last"><?php echo esc_attr__( 'Year', 'woocommerce-events' ); ?></a>  
				<input type="submit" value="Go">
			</div>
			<div class="form-field-checkbox">
			<input type="checkbox" id="fooevents-include-canceled-tickets" value="yes" checked /><label for="fooevents-include-canceled-tickets"><?php echo esc_attr__( 'Canceled tickets', 'woocommerce-events' ); ?></label>
			</div>
			<div class="clear"></div>
		</form> 
		<div class="stats">
			<div class="stat stat-1">
				<div class="inner">
					<label>Date</label><?php echo ( ! empty( $woocommerce_events_date ) ) ? '<h3>' . esc_attr( $woocommerce_events_date ) . '</h3>' : '<h3>' . esc_attr__( 'None', 'woocommerce-events' ) . '</h3>'; ?>
				</div>
			</div>
			<div class="stat stat-2">
				<div class="inner">
					<label><?php esc_attr_e( 'Location', 'woocommerce-events' ); ?></label><?php echo ( ! empty( $woocommerce_events_location ) ) ? '<h3>' . esc_attr( $woocommerce_events_location ) . '</h3>' : '<h3>' . esc_attr__( 'None', 'woocommerce-events' ) . '</h3>'; ?>
				</div>
			</div>
			<div class="stat stat-3">
				<div class="inner">
					<label><?php esc_attr_e( 'Net Revenue', 'woocommerce-events' ); ?></label><h3><span id="WooCommerceTicketsNetRevenue">--</span></h3>
				</div>
			</div>
			<div class="stat stat-3">
				<div class="inner">
					<label><?php esc_attr_e( 'Gross Revenue', 'woocommerce-events' ); ?></label><h3><span id="WooCommerceTicketsRevenue">--</span></h3>
				</div>
			</div>
			<div class="stat stat-4">
				<div class="inner">
					<label><?php esc_attr_e( 'Tickets', 'woocommerce-events' ); ?></label><h3><a href="<?php echo esc_attr( get_admin_url() ); ?>edit.php?post_type=event_magic_tickets&event_id=<?php echo esc_attr( $id ); ?>"><span id="WooCommerceTicketsSold">--</span></a></h3>
				</div>
			</div>
			<div class="stat stat-5">
				<div class="inner">
					<label><?php esc_attr_e( 'Check-ins', 'woocommerce-events' ); ?></label><h3><span id="WooCommerceCheckIns">--</span></h3>
				</div>
			</div>
			<div class="stat stat-6">
				<div class="inner">
					<label><?php esc_attr_e( 'Check-outs', 'woocommerce-events' ); ?></label><h3><span id="WooCommerceCheckOuts">--</span></h3>
				</div>
			</div>
			<div class="clear"></div>
		</div>        
		<div class="clear"></div>
		<div class="charts">
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Net Revenue', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-tickets-revenue-net" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
						<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Gross Revenue', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-tickets-revenue" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Tickets Sold', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-tickets-sold" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Check-ins (By Date Range)', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-check-ins" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Check-ins (Past 24 Hours)', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-check-ins-today" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Check-outs (By Date Range)', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-check-outs" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="chart">
				<div class="inner">
					<h3><?php esc_attr_e( 'Check-outs (Past 24 Hours)', 'woocommerce-events' ); ?> </h3>
					<div class="chart-container">
						<div class="fooevents-report-chart" id="fooevents-report-check-outs-today" style="width: 100%; height: 300px;"></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div> 
	<div class="clear"></div>
	<div id="icon-users" class="icon32"></div>
	<h2><?php esc_attr_e( 'Attendee Check-in Details', 'woocommerce-events' ); ?></h2>
	<?php $check_in_list_table->display(); ?>
</div>
