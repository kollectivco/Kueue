<?php
/**
 * Ticket Theme Header
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />   
	<meta name="viewport" content="width=device-width" initial-scale="1">
	<!--[if !mso]>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<![endif]-->
	<meta name="x-apple-disable-message-reformatting">
	<title></title>
	<!--[if mso]>
		<style>
			* { font-family: <?php echo wp_kses_post( $font_family ); ?>, sans-serif !important; }
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
		body, table, td, a{-webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;font-family:<?php echo wp_kses_post( $font_family ); ?>;font-size:14px;} /* Prevent WebKit and Windows mobile changing default text sizes */
		table, td{mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-spacing: 0; border-collapse: collapse; table-layout: fixed;  font-family:<?php echo wp_kses_post( $font_family ); ?>; font-size:14px;} /* Remove spacing between tables in Outlook 2007 and up */
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
		.btn {
			-webkit-transition: all 200ms ease;
			transition: all 200ms ease;
		}
		.btn:hover {
			background-color: dodgerblue;
		}
		.fooevents-pdf-email-text {
			margin:10px !important;
			font-size:14px !important;
			text-align: center !important;
			font-family:<?php echo wp_kses_post( $font_family ); ?> !important;
		} 
		.intro {
			padding: 30px; width: 100%; max-width: 700px; margin: 0 auto; font-size:14px; font-family: <?php echo wp_kses_post( $font_family ); ?>;
		}
		.clearfix {
			clear: both
		}
		@media screen and (max-width: 640px) {
			.container {
				width: 100%;
				margin: auto;
			}
			.wide,
			.wide td {
				display: block !important;
				width:100% !important;
				max-width: 100% !important;
				border-right-width: 0 !important;
				border-bottom-width: 1px !important;
			} 
			label {
				font-weight: bold;
			}	
		}
		@media print {    
			.no-print, .no-print *
			{
				display: none !important;
			}
			.container {
				max-width: 340px;
				width:100%;
				margin: auto;
			}
			.wide,
			.wide td {
				display: block !important;
				width:100% !important;
				max-width: 100% !important;
				border-right-width: 0 !important;
				border-bottom-width: 1px !important;
			} 
			label {
				font-weight: bold;
			}
		}   
	</style>		
</head>
<body style="text-align: center">
