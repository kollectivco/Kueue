<?php
/**
 * Attendee badges template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>FooEvents Attendee Badges</title>
	<style>
		html, body, div, h1, h2 {
			vertical-align: baseline;
			margin: 0;
			padding: 0;
			border: 0;
		}

		html {
			line-height: 1;
		}

		ol, ul {
			padding: 0;
			white-space: normal;
		}

		.fooevents_clear {
			clear: both;
		}

		.fooevents_letter_certificate_portrait_1, .fooevents_letter_certificate_portrait_1 .fooevents_ticket {
			width: 8.5in;
			height: 11in;
		}

		.fooevents_letter_certificate_landscape_1, .fooevents_letter_certificate_landscape_1 .fooevents_ticket {
			height: 8.5in;
			width: 11in;
		}

		.fooevents_a4_certificate_portrait_1, .fooevents_a4_certificate_portrait_1 .fooevents_ticket {
			width: 210mm;
			height: 297mm;
		}

		.fooevents_a4_certificate_landscape_1, .fooevents_a4_certificate_landscape_1 .fooevents_ticket {
			height: 210mm;
			width: 297mm;
		}

		.fooevents_letter_10, .fooevents_letter_30 {
			width: 8.5in;
			padding: .50in 0 0 .30in;
		}

		.fooevents_letter_6 {
			width: 8.25in;
			padding: 1in 0 0 .25in;
		}

		.fooevents_letter_labels_5 {
			width: 11in;
			padding: 0.375in 0 0 1.25in;
		}

		.fooevents_wristband_boca_1, .fooevents_wristband_boca_1 .fooevents_ticket {
			width: 11in;
			height: 1in;
		}

		.fooevents_tickets_boca_55_2 {
			width: 5.5in;
			height: 2in;
		}

		.fooevents_ticket_info.boca_55_2_stretch {
			flex-grow: 2;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket {
			width: 5.5in;
			height: 2in;
		}

		.fooevents_letter_labels_5 .fooevents_ticket {
			width: 9.75in;
			height: 1.45in;
			margin-bottom: 0.375in;
		}

		.fooevents_letter_labels_1,
		.fooevents_letter_labels_1 .fooevents_ticket {
			width: 10in;
			height: 1in;
			padding: 0;
		}

		.fooevents_dk2113_1 {
			height: 61mm;
			width: 101.6mm;
			padding: 0;
		}

		.fooevents_dk2113_1 .fooevents_ticket {
			/*transform: rotate(-90deg);
			transform-origin: top left;*/
			height: 61mm;
			width: 101.6mm;
			padding: 0;
		}	

		.fooevents_ticket_page.fooevents_ticket_line_on.fooevents_letter_labels_5 .fooevents_ticket {
			border-top: 1px dotted #cccccc;
			border-left: 1px dotted #cccccc;
		}

		.fooevents_letter_6 .fooevents_ticket {
			padding: .025in .3in 0;
			width: 4in;
			height: 3in;
		}

		.fooevents_a4_12 {
			width: 190.5mm;
			padding: 4.5mm 9.75mm;
		}

		.fooevents_a4_16 {
			width: 198mm;
			padding: 12.9mm 6mm;
		}

		.fooevents_a4_24 {
			width: 210mm;
			padding: 9mm 0 8mm 0;
		}

		.fooevents_a4_39 {
			width: 198mm;
			padding: 14.015mm 6mm;
		}

		.fooevents_a4_45 {
			width: 192.5mm;
			padding: 13.95mm 8.75mm;
		}

		.fooevents_a4_12 .fooevents_ticket {
			width: 63.5mm;
			height: 72mm;
		}

		.fooevents_a4_16 .fooevents_ticket {
			width: 99mm;
			height: 33.9mm;
		}

		.fooevents_a4_24 .fooevents_ticket {
			width: 70mm;
			height: 35mm;
		}

		.fooevents_a4_39 .fooevents_ticket {
			width: 66mm;
			height: 20.69mm;
		}

		.fooevents_a4_45 .fooevents_ticket {
			width: 38.5mm;
			height: 29.9mm;
		}

		.fooevents_badge_page, .fooevents_ticket_page {
			margin: 0;
			font-family: Arial, Sans-serif;
			page-break-after: always;
		}

		.fooevents_letter_10 .fooevents_ticket, .fooevents_letter_30 .fooevents_ticket {
			padding: .025in .3in 0;
			margin-right: .18in;
			margin-top: .025in;
		}

		.fooevents_badge {
			float: left;
			overflow: hidden;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			position: relative;
		}

		.fooevents_badge_line_on {
			outline: 1px dotted #ccc;
		}

		.fooevents_letter_10 .fooevents_ticket {
			width: 4.025in;
			height: 2.05in;
		}

		.fooevents_letter_30 .fooevents_ticket {
			width: 2.625in;
			height: 1in;
		}

		.fooevents_badge_16 .fooevents_event_car {
			margin: 7px auto;
			display: block;
		}

		.fooevents_badge_39 .fooevents_event_car, .fooevents_badge_45 .fooevents_event_car {
			margin: 3px auto;
			display: block;
		}

		.fooevents_badge_30 .fooevents_event_car, .fooevents_badge_24 .fooevents_event_car {
			font-size: 10px;
		}

		.fooevents_badge_10 .fooevents_event_car, .fooevents_badge_12 .fooevents_event_car {
			font-size: 16px;
			line-height: 20px;
		}

		.fooevents_badge_10 h3, .fooevents_badge_12 h3 {
			font-size: 18px;
			margin-top: 20px;
		}

		.fooevents_badge_30 img, .fooevents_badge_16 img, .fooevents_badge_24 img {
			width: 130px;
		}

		.fooevents_badge_39 img, .fooevents_badge_45 img {
			width: 100px;
		}

		.fooevents_badge_inner {
			margin: 0;
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			width: 90%;
			overflow-wrap: break-word;
			word-wrap: break-word;
		}

		.fooevents_ticket {
			float: left;
			overflow: hidden;
			-moz-box-sizing: border-box;
			-webkit-box-sizing: border-box;
			box-sizing: border-box;
			position: relative;
			background-size: 100%;
			background-repeat: no-repeat;
			<?php
			if ( '' !== $bgimg ) {
				echo "background-image: url('" . esc_attr( $bgimg ) . "')";}
			?>
		}

		.fooevents_ticket h1, .fooevents_ticket h3 {
			margin: 0px;
		}

		.fooevents_ticket_small, .fooevents_ticket_small_uppercase {
			font-size: 70%;
			line-height: 100%;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child .fooevents_ticket_small,
		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child .fooevents_ticket_small_uppercase {
			font-size: 60%;
		}
		


		.fooevents_ticket_medium, .fooevents_ticket_medium_uppercase {
			font-size: 100%;
			line-height: 100%;
			margin: 0px;
		}

		.fooevents_ticket_large, .fooevents_ticket_large_uppercase {
			font-size: 150%;
			line-height: 100%;
			font-weight: normal;
		}

		.fooevents_ticket_small b, .fooevents_ticket_small_uppercase b,
		.fooevents_ticket_medium b, .fooevents_ticket_medium_uppercase b,
		.fooevents_ticket_large b, .fooevents_ticket_large_uppercase b {
			padding-right: 5px;
		}

		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_small,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_small b,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_small_uppercase,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_small_uppercase b,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_medium b,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_medium_uppercase b,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_large b,
		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_large_uppercase b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_small,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_small b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_small_uppercase,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_small_uppercase b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_medium b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_medium_uppercase b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_large b,
		.fooevents_ticket_page.fooevents_tickets_boca_55_2 .fooevents_ticket_large_uppercase b {
			opacity: 1
		}

		.fooevents_ticket_small_uppercase, .fooevents_ticket_medium_uppercase, .fooevents_ticket_large_uppercase {
			text-transform: uppercase;
		}        

		.fooevents_ticket_hr {
			width: 30%;
			opacity: 0.3;
			margin-bottom: 0;
			margin-top: 10px;
		}

		.fooevents_ticket_spacer {
			width: 100%;
			height: 10px;
		}

		.fooevents_tickets_letter_10, .fooevents_tickets_avery_letter_10 {
			width: 11in;
			padding: 0;            
		}

		.fooevents_tickets_letter_10 .fooevents_ticket,
		.fooevents_tickets_avery_letter_10 .fooevents_ticket {
			width: 5.5in;
			height: 1.7in;
			position: relative;
		}

		.fooevents_tickets_a4_10 {
			width: 297mm;
			padding: 0;            
		}

		.fooevents_tickets_a4_3 {
			width: 210mm;
			padding: 0;            
		}

		.fooevents_tickets_a4_10 .fooevents_ticket {
			width: 148.4mm;
			height: 42mm;
			position: relative;
		}

		.fooevents_tickets_a4_3 .fooevents_ticket {
			width: 210mm;
			height: 99mm;
			position: relative;
		}

		.fooevents_ticket_page.fooevents_ticket_line_on .fooevents_ticket,
		.fooevents_ticket_page.fooevents_wristband_boca_1.fooevents_ticket_line_on .fooevents_ticket {
			border-bottom: 1px dotted #cccccc;
			border-right: 1px dotted #cccccc;
		}

		.fooevents_ticket_page.fooevents_tickets_a4_3.fooevents_ticket_line_on .fooevents_ticket:nth-child(3) {
			border-bottom: 0px dotted #cccccc;
		}

		.fooevents_ticket_page.fooevents_letter_6.fooevents_ticket_line_on .fooevents_ticket,
		.fooevents_ticket_page.fooevents_letter_10.fooevents_ticket_line_on .fooevents_ticket,
		.fooevents_ticket_page.fooevents_a4_12.fooevents_ticket_line_on .fooevents_ticket:nth-child(3n+1),
		.fooevents_ticket_page.fooevents_a4_16.fooevents_ticket_line_on .fooevents_ticket:nth-child(2n+1),
		.fooevents_ticket_page.fooevents_a4_39.fooevents_ticket_line_on .fooevents_ticket:nth-child(3n+1),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(5n+1),
		.fooevents_ticket_page.fooevents_letter_30.fooevents_ticket_line_on .fooevents_ticket {
			border-left: 1px dotted #cccccc;
		}

		.fooevents_ticket_page.fooevents_letter_6.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_letter_6.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_12.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_a4_12.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_12.fooevents_ticket_line_on .fooevents_ticket:nth-child(3),
		.fooevents_ticket_page.fooevents_a4_16.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_a4_16.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_24.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_a4_24.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_24.fooevents_ticket_line_on .fooevents_ticket:nth-child(3),
		.fooevents_ticket_page.fooevents_a4_39.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_a4_39.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_39.fooevents_ticket_line_on .fooevents_ticket:nth-child(3),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(1),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(2),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(3),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(4),
		.fooevents_ticket_page.fooevents_a4_45.fooevents_ticket_line_on .fooevents_ticket:nth-child(5),
		.fooevents_ticket_page.letter_30.fooevents_ticket_line_on .fooevents_ticket {
			border-top: 1px dotted #cccccc;
		}

		.fooevents_ticket_page.fooevents_letter_10.fooevents_ticket_line_on .fooevents_ticket {
			border: none;
			outline: 1px dotted #cccccc;
		}

		.fooevents_ticket_inner {
			display:flex;
			flex-direction: row;
			flex-wrap: nowrap;
			height: 100%;
		}

		.fooevents_ticket_page.fooevents_wristband_boca_1 .fooevents_ticket_inner {
			width: 65%;
			margin-left: 80px;
		}

		.fooevents_ticket_info {
			flex-grow: 1;
			flex-basis: 0;
			box-sizing: border-box;
			display:flex;
			flex-direction: column;
			flex-wrap: nowrap;
			text-align: center;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:nth-child(4) {
			width: 1in;
		}

		.fooevents_ticket_info div {
			flex-grow: 1;
			flex-basis: 0;
			padding: 3%;
			box-sizing: border-box;
			min-height: 0;
			justify-content: center;
			align-items: center;
			display: flex;
		}

		.fooevents_ticket_info div.fooevents_wysiwyg {
			flex-direction: column;
		}

		.fooevents_ticket_info div.fooevents_wysiwyg p {
			margin: 5px 0;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info div {
			padding: 5%;
		}

		.fooevents_ticket_email {
			word-wrap: anywhere;
		}

		.fooevents_ticket_info div img {
			max-width: 100%;
			max-height: 100%;
			display: block;
			margin: auto;
		}

		.fooevents_tickets_letter_10 .fooevents_ticket_info:last-child {
			max-width: 1.5in;
			min-width: 1.5in;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child {
			max-width: 0.9in;
			min-width: 0.9in;
			padding: 1%;
			
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child div,
		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child b,
		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child strong,
		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child u,
		.fooevents_tickets_boca_55_2 .fooevents_ticket_info:last-child span {
			display: block;
		}

		.fooevents_tickets_boca_55_2 .fooevents_ticket_info p {
			margin: 0;
			padding: 0;
		}

		.fooevents_tickets_letter_10.fooevents_ticket_line_on .fooevents_ticket_info:last-child {
			border-left: 1px dotted #aaaaaa;
		}
	</style>
	<script type="text/javascript">
		function printFunction() { 
			window.print();
		}
	</script>
</head>
<body onload="printFunction();">
	<?php

		$content_to_echo1  = '';
		$content_to_echo2  = '';
		$content_to_echo3  = '';
		$content_to_echo4  = '';
		$content_to_echo5  = '';
		$content_to_echo6  = '';
		$content_to_echo7  = '';
		$content_to_echo8  = '';
		$content_to_echo9  = '';
		$content_to_echo10 = '';
		$content_to_echo11 = '';
		$content_to_echo12 = '';
		$content_to_echo13 = '';
		$content_to_echo14 = '';
		$content_to_echo15 = '';
		$content_to_echo16 = '';
		$event             = '';
		$event_date        = '';
		$event_var         = '';
		$var_only          = '';
		$barcode           = '';
		$logo              = '';
		$ticketnr          = '';
		$custom_ticketnr   = '';
		$name              = '';
		$email             = '';
		$phone             = '';
		$company           = '';
		$designation       = '';
		$seat              = '';
		$location          = '';
		$custom            = '';
		$spacer            = '';

	foreach ( $sorted_rows as $item ) {

		$ticketnr        = '<p><b>' . esc_attr__( 'Ticket:', 'woocommerce-events' ) . '</b> ' . $item['TicketID'] . '</p>';
		$custom_ticketnr = '<p><b>' . esc_attr__( 'Ticket:', 'woocommerce-events' ) . '</b> ' . $item['CustomTicketID'] . '</p>';

		if ( ! empty( $item['TicketHash'] ) ) {

			$item['TicketHash'] .= '-';

		}

		$barcode     = "<div><img src='" . esc_url( $this->config->barcode_url ) . $item['TicketHash'] . str_replace( '#', '', $item['TicketID'] ) . ".png' /></div>";
		$name        = urldecode( $item['Attendee First Name'] ) . ' ' . urldecode( $item['Attendee Last Name'] );
		$event       = urldecode( $item['Event Name'] );
		$event_date  = urldecode( $item['Event Date'] );
		$event_var   = urldecode( $item['Event Name Variations'] );
		$var_only    = urldecode( $item['Variation'] );
		$email       = $item['Attendee Email'];
		$phone       = $item['Attendee Telephone'];
		$company     = urldecode( $item['Attendee Company'] );
		$designation = urldecode( $item['Attendee Designation'] );
		for ( $i = 0; $i < 16; $i++ ) {
			${'seat' . $i + 1} = isset( $item['SeatInfo'][ $i ] ) ? urldecode( $item['SeatInfo'][ $i ] ) : '';
		}

		$location = urldecode( $item['Location'] );
		$spacer   = '<div></div>';
		$custom1  = $item['Custom1'];
		$custom2  = $item['Custom2'];
		$custom3  = $item['Custom3'];
		$custom4  = $item['Custom4'];
		$custom5  = $item['Custom5'];
		$custom6  = $item['Custom6'];
		$custom7  = $item['Custom7'];
		$custom8  = $item['Custom8'];
		$custom9  = $item['Custom9'];
		$custom10 = $item['Custom10'];
		$custom11 = $item['Custom11'];
		$custom12 = $item['Custom12'];
		$custom13 = $item['Custom13'];
		$custom14 = $item['Custom14'];
		$custom15 = $item['Custom15'];
		$custom16 = $item['Custom16'];

		if ( '' !== $ticketfield1 ) {
			switch ( $ticketfield1 ) {
				case 'barcode':
					$content_to_echo1 = $barcode;
					break;
				case 'logo':
					$content_to_echo1 = '<div><img class="fooevents_ticket_logo" src=' . $logo1 . ' /></div>';
					break;
				case 'event':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $event . '</div>';
					break;
				case 'date':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $event_date . '</div>';
					break;
				case 'event_var':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $event_var . '</div>';
					break;
				case 'var_only':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $var_only . '</div>';
					break;
				case 'ticketnr':
					$content_to_echo1 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font1 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
					break;
				case 'custom_ticketnr':
					$content_to_echo1 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font1 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
					break;
				case 'name':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $name . '</div>';
					break;
				case 'email':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . " fooevents_ticket_email'>" . $email . '</div>';
					break;
				case 'phone':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . " fooevents_ticket_phone'>" . $phone . '</div>';
					break;
				case 'company':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . " fooevents_ticket_company'>" . $company . '</div>';
					break;
				case 'designation':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . " fooevents_ticket_designation'>" . $designation . '</div>';
					break;
				case 'seat':
					$content_to_echo1 = '' !== $seat1 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font1 . "'><span>" . $seat1 . '</span></div>' : '';
					break;
				case 'location':
					$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'>" . $location . '</div>';
					break;
				case 'custom':
					$content_to_echo1 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font1 . "'>" . $custom1 . '</div>';
					break;
				case 'spacer':
					$content_to_echo1 = $spacer;
					break;
				case stristr( $ticketfield1, 'fooevents_custom' ):
					$cf       = explode( ':', $item[ $ticketfield1 ] );
					$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

					if ( ! empty( $item[ $ticketfield1 ] ) ) {

						$content_to_echo1 = "<div class='fooevents_ticket_" . $font1 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

					} else {

						$content_to_echo1 = '';

					}
					break;
			}
		}

		if ( $nrrow > 1 ) {

			if ( '' !== $ticketfield2 ) {
				switch ( $ticketfield2 ) {
					case 'barcode':
						$content_to_echo2 = $barcode;
						break;
					case 'logo':
						$content_to_echo2 = '<div><img class="fooevents_ticket_logo" src=' . $logo2 . ' /></div>';
						break;
					case 'event':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo2 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font2 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo2 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font2 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo2 = '' !== $seat2 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font2 . "'><span>" . $seat2 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo2 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font2 . "'>" . $custom2 . '</div>';
						break;
					case 'spacer':
						$content_to_echo2 = $spacer;
						break;
					case stristr( $ticketfield2, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield2 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield2 ] ) ) {

							$content_to_echo2 = "<div class='fooevents_ticket_" . $font2 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo2 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 2 ) {

			if ( '' !== $ticketfield3 ) {
				switch ( $ticketfield3 ) {
					case 'barcode':
						$content_to_echo3 = $barcode;
						break;
					case 'logo':
						$content_to_echo3 = '<div><img class="fooevents_ticket_logo" src=' . $logo3 . ' /></div>';
						break;
					case 'event':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $event . '</div>';
						break;
					case 'event_var':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $event_var . '</div>';
						break;
					case 'date':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $event_date . '</div>';
						break;
					case 'var_only':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo3 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font3 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo3 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font3 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . " ticket_email'>" . $email . '</div>';
						break;
					case 'seat':
						$content_to_echo3 = '' !== $seat3 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font3 . "'><span>" . $seat3 . '</span></div>' : '';
						break;
					case 'phone':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'location':
						$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo3 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font3 . "'>" . $custom3 . '</div>';
						break;
					case 'spacer':
						$content_to_echo3 = $spacer;
						break;
					case stristr( $ticketfield3, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield3 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield3 ] ) ) {

							$content_to_echo3 = "<div class='fooevents_ticket_" . $font3 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo3 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 3 ) {

			if ( '' !== $ticketfield4 ) {
				switch ( $ticketfield4 ) {
					case 'barcode':
						$content_to_echo4 = $barcode;
						break;
					case 'logo':
						$content_to_echo4 = '<div><img class="fooevents_ticket_logo" src=' . $logo4 . ' /></div>';
						break;
					case 'event':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $event . '</div>';
						break;
					case 'event_var':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $event_var . '</div>';
						break;
					case 'date':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $event_date . '</div>';
						break;
					case 'var_only':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo4 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font4 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo4 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font4 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo4 = '' !== $seat4 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font4 . "'><span>" . $seat4 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo4 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font4 . "'>" . $custom4 . '</div>';
						break;
					case 'spacer':
						$content_to_echo4 = $spacer;
						break;
					case stristr( $ticketfield4, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield4 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield4 ] ) ) {

							$content_to_echo4 = "<div class='fooevents_ticket_" . $font4 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo4 = '';

						}
						break;
				}
			}
		}

		if ( $nrcol > 1 ) {

			if ( '' !== $ticketfield5 ) {
				switch ( $ticketfield5 ) {
					case 'barcode':
						$content_to_echo5 = $barcode;
						break;
					case 'logo':
						$content_to_echo5 = '<div><img class="fooevents_ticket_logo" src=' . $logo5 . ' /></div>';
						break;
					case 'event':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo5 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font5 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo5 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font5 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo5 = '' !== $seat5 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font5 . "'><span>" . $seat5 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo5 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font5 . "'>" . $custom5 . '</div>';
						break;
					case 'spacer':
						$content_to_echo5 = $spacer;
						break;
					case stristr( $ticketfield5, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield5 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield5 ] ) ) {

							$content_to_echo5 = "<div class='fooevents_ticket_" . $font5 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo5 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 1 && $nrcol > 1 ) {

			if ( '' !== $ticketfield6 ) {
				switch ( $ticketfield6 ) {
					case 'barcode':
						$content_to_echo6 = $barcode;
						break;
					case 'logo':
						$content_to_echo6 = '<div><img class="fooevents_ticket_logo" src=' . $logo6 . ' /></div>';
						break;
					case 'event':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo6 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font6 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo6 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font6 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo6 = '' !== $seat6 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font6 . "'><span>" . $seat6 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo6 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font6 . "'>" . $custom6 . '</div>';
						break;
					case 'spacer':
						$content_to_echo6 = $spacer;
						break;
					case stristr( $ticketfield6, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield6 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield6 ] ) ) {

							$content_to_echo6 = "<div class='fooevents_ticket_" . $font6 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo6 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 2 && $nrcol > 1 ) {

			if ( '' !== $ticketfield7 ) {
				switch ( $ticketfield7 ) {
					case 'barcode':
						$content_to_echo7 = $barcode;
						break;
					case 'logo':
						$content_to_echo7 = '<div><img class="fooevents_ticket_logo" src=' . $logo7 . ' /></div>';
						break;
					case 'event':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo7 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font7 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo7 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font7 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo7 = '' !== $seat7 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font7 . "'><span>" . $seat7 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo7 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font7 . "'>" . $custom7 . '</div>';
						break;
					case 'spacer':
						$content_to_echo7 = $spacer;
						break;
					case stristr( $ticketfield7, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield7 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield7 ] ) ) {

							$content_to_echo7 = "<div class='fooevents_ticket_" . $font7 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo7 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 3 && $nrcol > 1 ) {

			if ( '' !== $ticketfield8 ) {
				switch ( $ticketfield8 ) {
					case 'barcode':
						$content_to_echo8 = $barcode;
						break;
					case 'logo':
						$content_to_echo8 = '<div><img class="fooevents_ticket_logo" src=' . $logo8 . ' /></div>';
						break;
					case 'event':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo8 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font8 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo8 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font8 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo8 = '' !== $seat8 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font8 . "'><span>" . $seat8 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo8 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font8 . "'>" . $custom8 . '</div>';
						break;
					case 'spacer':
						$content_to_echo8 = $spacer;
						break;
					case stristr( $ticketfield8, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield8 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield8 ] ) ) {

							$content_to_echo8 = "<div class='fooevents_ticket_" . $font8 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo8 = '';

						}
						break;
				}
			}
		}

		if ( $nrcol > 2 ) {

			if ( '' !== $ticketfield9 ) {
				switch ( $ticketfield9 ) {
					case 'barcode':
						$content_to_echo9 = $barcode;
						break;
					case 'logo':
						$content_to_echo9 = '<div><img class="fooevents_ticket_logo" src=' . $logo9 . ' /></div>';
						break;
					case 'event':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo9 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font9 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo9 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font9 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo9 = '' !== $seat9 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font9 . "'><span>" . $seat9 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo9 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font9 . "'>" . $custom9 . '</div>';
						break;
					case 'spacer':
						$content_to_echo9 = $spacer;
						break;
					case stristr( $ticketfield9, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield9 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield9 ] ) ) {

							$content_to_echo9 = "<div class='fooevents_ticket_" . $font9 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo9 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 1 && $nrcol > 2 ) {

			if ( '' !== $ticketfield10 ) {
				switch ( $ticketfield10 ) {
					case 'barcode':
						$content_to_echo10 = $barcode;
						break;
					case 'logo':
						$content_to_echo10 = '<div><img class="fooevents_ticket_logo" src=' . $logo10 . ' /></div>';
						break;
					case 'event':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo10 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font10 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo10 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font10 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo10 = '' !== $seat10 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font10 . "'><span>" . $seat10 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo10 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font10 . "'>" . $custom10 . '</div>';
						break;
					case 'spacer':
						$content_to_echo10 = $spacer;
						break;
					case stristr( $ticketfield10, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield10 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield10 ] ) ) {

							$content_to_echo10 = "<div class='fooevents_ticket_" . $font10 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo10 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 2 && $nrcol > 2 ) {

			if ( '' !== $ticketfield11 ) {
				switch ( $ticketfield11 ) {
					case 'barcode':
						$content_to_echo11 = $barcode;
						break;
					case 'logo':
						$content_to_echo11 = '<div><img class="fooevents_ticket_logo" src=' . $logo11 . ' /></div>';
						break;
					case 'event':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo11 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font11 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo11 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font11 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo11 = '' !== $seat11 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font11 . "'><span>" . $seat11 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo11 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font11 . "'>" . $custom11 . '</div>';
						break;
					case 'spacer':
						$content_to_echo11 = $spacer;
						break;
					case stristr( $ticketfield11, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield11 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield11 ] ) ) {

							$content_to_echo11 = "<div class='fooevents_ticket_" . $font11 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo11 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 3 && $nrcol > 2 ) {

			if ( '' !== $ticketfield12 ) {
				switch ( $ticketfield12 ) {
					case 'barcode':
						$content_to_echo12 = $barcode;
						break;
					case 'logo':
						$content_to_echo12 = '<div><img class="fooevents_ticket_logo" src=' . $logo12 . ' /></div>';
						break;
					case 'event':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo12 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font12 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo12 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font12 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo12 = '' !== $seat12 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font12 . "'><span>" . $seat12 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'>" . $custom12 . '</div>';
						break;
					case 'spacer':
						$content_to_echo12 = $spacer;
						break;
					case stristr( $ticketfield12, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield12 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield12 ] ) ) {

							$content_to_echo12 = "<div class='fooevents_ticket_" . $font12 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo12 = '';

						}
						break;
				}
			}
		}

		if ( $nrcol > 3 ) {

			if ( '' !== $ticketfield13 ) {
				switch ( $ticketfield13 ) {
					case 'barcode':
						$content_to_echo13 = $barcode;
						break;
					case 'logo':
						$content_to_echo13 = '<div><img class="fooevents_ticket_logo" src=' . $logo13 . ' /></div>';
						break;
					case 'event':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo13 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font13 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo13 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font13 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo13 = '' !== $seat13 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font13 . "'><span>" . $seat13 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo13 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font13 . "'>" . $custom13 . '</div>';
						break;
					case 'spacer':
						$content_to_echo13 = $spacer;
						break;
					case stristr( $ticketfield13, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield13 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield13 ] ) ) {

							$content_to_echo13 = "<div class='fooevents_ticket_" . $font13 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo13 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 1 && $nrcol > 3 ) {

			if ( '' !== $ticketfield14 ) {
				switch ( $ticketfield14 ) {
					case 'barcode':
						$content_to_echo14 = $barcode;
						break;
					case 'logo':
						$content_to_echo14 = '<div><img class="fooevents_ticket_logo" src=' . $logo14 . ' /></div>';
						break;
					case 'event':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo14 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font14 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo14 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font14 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo14 = '' !== $seat14 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font14 . "'><span>" . $seat14 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo14 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font14 . "'>" . $custom14 . '</div>';
						break;
					case 'spacer':
						$content_to_echo14 = $spacer;
						break;
					case stristr( $ticketfield14, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield14 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield14 ] ) ) {

							$content_to_echo14 = "<div class='fooevents_ticket_" . $font14 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo14 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 2 && $nrcol > 3 ) {

			if ( '' !== $ticketfield15 ) {
				switch ( $ticketfield15 ) {
					case 'barcode':
						$content_to_echo15 = $barcode;
						break;
					case 'logo':
						$content_to_echo15 = '<div><img class="fooevents_ticket_logo" src=' . $logo15 . ' /></div>';
						break;
					case 'event':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo15 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font15 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo15 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font15 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo15 = '' !== $seat15 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font15 . "'><span>" . $seat15 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo15 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font15 . "'>" . $custom15 . '</div>';
						break;
					case 'spacer':
						$content_to_echo15 = $spacer;
						break;
					case stristr( $ticketfield15, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield15 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield15 ] ) ) {

							$content_to_echo15 = "<div class='fooevents_ticket_" . $font15 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo15 = '';

						}
						break;
				}
			}
		}

		if ( $nrrow > 3 && $nrcol > 3 ) {

			if ( '' !== $ticketfield16 ) {
				switch ( $ticketfield16 ) {
					case 'barcode':
						$content_to_echo16 = $barcode;
						break;
					case 'logo':
						$content_to_echo16 = '<div><img class="fooevents_ticket_logo" src=' . $logo16 . ' /></div>';
						break;
					case 'event':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $event . '</div>';
						break;
					case 'date':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $event_date . '</div>';
						break;
					case 'event_var':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $event_var . '</div>';
						break;
					case 'var_only':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $var_only . '</div>';
						break;
					case 'ticketnr':
						$content_to_echo16 = '' !== $item['TicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font16 . "'><b>" . esc_attr__( 'Ticket', 'woocommerce-events' ) . '</b><span>' . $item['TicketID'] . '</span></div>' : '';
						break;
					case 'custom_ticketnr':
						$content_to_echo16 = '' !== $item['CustomTicketID'] ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font16 . "'><span>" . $item['CustomTicketID'] . '</span></div>' : '';
						break;
					case 'name':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $name . '</div>';
						break;
					case 'email':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . " fooevents_ticket_email'>" . $email . '</div>';
						break;
					case 'phone':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . " fooevents_ticket_phone'>" . $phone . '</div>';
						break;
					case 'company':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . " fooevents_ticket_company'>" . $company . '</div>';
						break;
					case 'designation':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . " fooevents_ticket_designation'>" . $designation . '</div>';
						break;
					case 'seat':
						$content_to_echo16 = '' !== $seat16 ? "<div class='fooevents_ticket_seat fooevents_ticket_" . $font16 . "'><span>" . $seat16 . '</span></div>' : '';
						break;
					case 'location':
						$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'>" . $location . '</div>';
						break;
					case 'custom':
						$content_to_echo16 = "<div class='fooevents_wysiwyg fooevents_ticket_" . $font16 . "'>" . $custom16 . '</div>';
						break;
					case 'spacer':
						$content_to_echo16 = $spacer;
						break;
					case stristr( $ticketfield16, 'fooevents_custom' ):
						$cf       = explode( ':', $item[ $ticketfield16 ] );
						$cf_label = ucwords( str_replace( '_', ' ', $cf[0] ) );

						if ( ! empty( $item[ $ticketfield16 ] ) ) {

							$content_to_echo16 = "<div class='fooevents_ticket_" . $font16 . "'><b>" . $cf_label . ': </b><span>' . $cf[1] . '</span></div>';

						} else {

							$content_to_echo16 = '';

						}
						break;
				}
			}
		}



		if ( 0 === $y ) {

			echo '<div class="fooevents_ticket_page ' . esc_attr( $page_size ) . ' fooevents_ticket_line_' . esc_attr( $cutlines ) . '">';

		} elseif ( 0 === $y % $nr_per_page ) {

			echo '</div><div class="fooevents_ticket_page ' . esc_attr( $page_size ) . ' fooevents_ticket_line_' . esc_attr( $cutlines ) . '">';

		}

		?>
		<div class="fooevents_ticket">
			<div class="fooevents_ticket_inner">    
			<?php if ( ! empty( $content_to_echo1 ) || ( ! empty( $content_to_echo2 ) ) || ( ! empty( $content_to_echo3 ) ) || ( ! empty( $content_to_echo4 ) ) ) { ?>
				<div class="fooevents_ticket_info">
					<?php
						echo wp_kses_post( $content_to_echo1 );
						echo wp_kses_post( $content_to_echo2 );
						echo wp_kses_post( $content_to_echo3 );
						echo wp_kses_post( $content_to_echo4 );
					?>
				</div>
				<?php } ?>
				<?php if ( ! empty( $content_to_echo5 ) || ( ! empty( $content_to_echo6 ) ) || ( ! empty( $content_to_echo7 ) ) || ( ! empty( $content_to_echo8 ) ) ) { ?>
				<div class="fooevents_ticket_info
					<?php
					if ( empty( $content_to_echo13 ) && empty( $content_to_echo14 ) && empty( $content_to_echo15 ) && empty( $content_to_echo16 ) && $page_size == 'fooevents_tickets_boca_55_2' ) {
						echo ' boca_55_2_stretch'; }
					?>
					">
					<?php
						echo wp_kses_post( $content_to_echo5 );
						echo wp_kses_post( $content_to_echo6 );
						echo wp_kses_post( $content_to_echo7 );
						echo wp_kses_post( $content_to_echo8 );
					?>
				</div>
				<?php } ?>
				<?php if ( ! empty( $content_to_echo9 ) || ( ! empty( $content_to_echo10 ) ) || ( ! empty( $content_to_echo11 ) ) || ( ! empty( $content_to_echo12 ) ) ) { ?>
				<div class="fooevents_ticket_info">
					<?php
						echo wp_kses_post( $content_to_echo9 );
						echo wp_kses_post( $content_to_echo10 );
						echo wp_kses_post( $content_to_echo11 );
						echo wp_kses_post( $content_to_echo12 );
					?>
				</div>
				<?php } ?>
				<?php if ( ! empty( $content_to_echo13 ) || ( ! empty( $content_to_echo14 ) ) || ( ! empty( $content_to_echo15 ) ) || ( ! empty( $content_to_echo16 ) ) ) { ?>
				<div class="fooevents_ticket_info">
					<?php
						echo wp_kses_post( $content_to_echo13 );
						echo wp_kses_post( $content_to_echo14 );
						echo wp_kses_post( $content_to_echo15 );
						echo wp_kses_post( $content_to_echo16 );
					?>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php
		++$y;
	}
	?>
	<div class="fooevents_clear"></div>
	</div>
</body>
</html>


