<?php
/**
 * Header template for receipt ticket theme
 *
 * @link https://www.fooevents.com
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates/receipt_ticket_theme
 */

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width" initial-scale="1">
	<!--[if !mso]>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<![endif]-->
	<meta name="x-apple-disable-message-reformatting">
	<title></title>
	<!--[if mso]>
		<style>
			* { font-family: <?php echo esc_html( $font_family ); ?>, sans-serif !important; }
		</style>
	<![endif]-->
	<style type="text/css" id="hs-inline-css">
		*,
		*:after,
		*:before {
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}
		* {
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		} 
		body, table, td, a{-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;font-family:<?php echo esc_attr( $font_family ); ?>;font-size:12px;} /* Prevent WebKit and Windows mobile changing default text sizes */
		table, td{mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-spacing: 0; border-collapse: collapse; table-layout: fixed;  font-family:<?php echo esc_attr( $font_family ); ?>; font-size:12px; } /* Remove spacing between tables in Outlook 2007 and up */
		img{-ms-interpolation-mode: bicubic; max-width: 100%; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none;} /* Allow smoother rendering of resized image in Internet Explorer */

		/* RESET STYLES */ 
		html, 
		body{height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important;}
		body {-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;text-rendering: optimizeLegibility;} 
		*[x-apple-data-detectors] {
			color: inherit !important;
			text-decoration: none !important;
		}
		.x-gmail-data-detectors,
		.x-gmail-data-detectors *,
		.aBn {
			border-bottom: 0 !important;
			cursor: default !important;
		}
		.clearfix {
			clear: both
		}
		@media screen and (max-width: 750px) {
			.container {
				width: 100%;
				margin: 0 auto;
			}
			.stack {
				display: block;
				width: 100%;
				max-width: 100%;
			}
		}
		@media print {    
			.no-print, .no-print *
			{
				display: none !important;
			}
		}  
	</style>
</head>
<body>
