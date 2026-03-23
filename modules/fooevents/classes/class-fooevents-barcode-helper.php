<?php
/**
 * Barcode generation class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Barcode generation class
 */
class FooEvents_Barcode_Helper {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	public $config;

	/**
	 * Barcode output method
	 *
	 * @var string $barcode_output_method
	 */
	public $barcode_output_method;

	/**
	 * On plugin load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		$this->barcode_output_method = get_option( 'globalWooCommerceEventsBarcodeOutput' );
	}

	/**
	 * Generate barcode
	 *
	 * @param string $text barcode value.
	 * @param string $prefix prefix.
	 * @param string $filename the filename.
	 * @param string $file_identifier the file identifier.
	 * @param int    $size barcode size.
	 * @param string $orientation orientation.
	 */
	public function generate_barcode( $text = 0, $prefix = '', $filename = '', $file_identifier = '', $size = 50, $orientation = 'horizontal' ) {

		$globa_woocommerce_events_enable_qr_code = get_option( 'globalWooCommerceEventsEnableQRCode' );

		if ( 'yes' === $globa_woocommerce_events_enable_qr_code && extension_loaded( 'gd' ) ) {

			$this->generate_qr( $text, $prefix, $filename, $file_identifier );

		} else {

			$this->generate_1d( $text, $prefix, $filename, $file_identifier, $size, $orientation );

		}

	}

	/**
	 * Generate QR Code
	 *
	 * @param string $text QR code value.
	 * @param string $prefix prefix.
	 * @param string $filename the filename.
	 * @param string $file_identifier the file identifier.
	 */
	public function generate_qr( $text = 0, $prefix = '', $filename = '', $file_identifier = '' ) {

		if ( ! class_exists( 'Foo_QRcode' ) ) {

			require_once $this->config->vendor_path . '/phpqrcode/phpqrcode.php';

		}

		$filename_output = '';
		if ( ! empty( $filename ) && ! empty( $file_identifier ) ) {

			$filename_output = $prefix . '-' . $filename . '-' . $file_identifier;

		} elseif ( ! empty( $prefix ) ) {

			$filename_output = $prefix . '-' . $text;

		} else {

			$filename_output = $text;

		}

		Foo_QRcode::png( $text, $this->config->barcode_path . $filename_output . '.png', Foo_QR_ECLEVEL_L, 8, 1 );

		if ( 'pngjpg' === $this->barcode_output_method ) {

			$this->png2jpg( $this->config->barcode_path . $filename_output . '.png', $this->config->barcode_path . $filename_output . '.jpg' );

		}

	}

	/**
	 * Generates ticket barcode.
	 * Originally modified from https://github.com/davidscotttufts/php-barcode/
	 *
	 * @param string $text barcode value.
	 * @param string $prefix prefix.
	 * @param string $filename the filename.
	 * @param string $file_identifier the file identifier.
	 * @param int    $size barcode size.
	 * @param string $orientation orientation.
	 */
	public function generate_1d( $text = 0, $prefix = '', $filename = '', $file_identifier = '', $size = 50, $orientation = 'horizontal' ) {

		$chksum = 104;
		// Must not change order of array elements as the checksum depends on the array's key to validate final code.
		$code_array  = array(
			' '       => '212222',
			'!'       => '222122',
			'"'       => '222221',
			'#'       => '121223',
			'$'       => '121322',
			'%'       => '131222',
			'&'       => '122213',
			"'"       => '122312',
			'('       => '132212',
			')'       => '221213',
			'*'       => '221312',
			'+'       => '231212',
			','       => '112232',
			'-'       => '122132',
			'.'       => '122231',
			'/'       => '113222',
			'0'       => '123122',
			'1'       => '123221',
			'2'       => '223211',
			'3'       => '221132',
			'4'       => '221231',
			'5'       => '213212',
			'6'       => '223112',
			'7'       => '312131',
			'8'       => '311222',
			'9'       => '321122',
			':'       => '321221',
			';'       => '312212',
			'<'       => '322112',
			'='       => '322211',
			'>'       => '212123',
			'?'       => '212321',
			'@'       => '232121',
			'A'       => '111323',
			'B'       => '131123',
			'C'       => '131321',
			'D'       => '112313',
			'E'       => '132113',
			'F'       => '132311',
			'G'       => '211313',
			'H'       => '231113',
			'I'       => '231311',
			'J'       => '112133',
			'K'       => '112331',
			'L'       => '132131',
			'M'       => '113123',
			'N'       => '113321',
			'O'       => '133121',
			'P'       => '313121',
			'Q'       => '211331',
			'R'       => '231131',
			'S'       => '213113',
			'T'       => '213311',
			'U'       => '213131',
			'V'       => '311123',
			'W'       => '311321',
			'X'       => '331121',
			'Y'       => '312113',
			'Z'       => '312311',
			'['       => '332111',
			'\\'      => '314111',
			']'       => '221411',
			'^'       => '431111',
			'_'       => '111224',
			'\`'      => '111422',
			'a'       => '121124',
			'b'       => '121421',
			'c'       => '141122',
			'd'       => '141221',
			'e'       => '112214',
			'f'       => '112412',
			'g'       => '122114',
			'h'       => '122411',
			'i'       => '142112',
			'j'       => '142211',
			'k'       => '241211',
			'l'       => '221114',
			'm'       => '413111',
			'n'       => '241112',
			'o'       => '134111',
			'p'       => '111242',
			'q'       => '121142',
			'r'       => '121241',
			's'       => '114212',
			't'       => '124112',
			'u'       => '124211',
			'v'       => '411212',
			'w'       => '421112',
			'x'       => '421211',
			'y'       => '212141',
			'z'       => '214121',
			'{'       => '412121',
			'|'       => '111143',
			'}'       => '111341',
			'~'       => '131141',
			'DEL'     => '114113',
			'FNC 3'   => '114311',
			'FNC 2'   => '411113',
			'SHIFT'   => '411311',
			'CODE C'  => '113141',
			'FNC 4'   => '114131',
			'CODE A'  => '311141',
			'FNC 1'   => '411131',
			'Start A' => '211412',
			'Start B' => '211214',
			'Start C' => '211232',
			'Stop'    => '2331112',
		);
		$code_keys   = array_keys( $code_array );
		$code_values = array_flip( $code_keys );
		$code_string = '';

		$text_length = strlen( $text );
		for ( $x = 1; $x <= $text_length; $x++ ) {

			$active_key   = substr( $text, ( $x - 1 ), 1 );
			$code_string .= $code_array[ $active_key ];
			$chksum       = ( $chksum + ( $code_values[ $active_key ] * $x ) );

		}

		$code_string .= $code_array[ $code_keys[ ( $chksum - ( intval( $chksum / 103 ) * 103 ) ) ] ];

		$code_string = '211214' . $code_string . '2331112';

		$code_length        = 20;
		$code_string_length = strlen( $code_string );
		for ( $i = 1; $i <= $code_string_length; $i++ ) {

			$code_length = $code_length + (int) ( substr( $code_string, ( $i - 1 ), 1 ) );

		}

		if ( strtolower( $orientation ) === 'horizontal' ) {

			$img_width  = $code_length;
			$img_height = $size;

		} else {

			$img_width  = $size;
			$img_height = $code_length;

		}

		$image = imagecreate( $img_width, $img_height + 10 );
		$black = imagecolorallocate( $image, 0, 0, 0 );
		$white = imagecolorallocate( $image, 255, 255, 255 );

		imagefill( $image, 0, 0, $white );

		$location           = 10;
		$code_string_length = strlen( $code_string );
		for ( $position = 1; $position <= $code_string_length; $position++ ) {

			$cur_size = $location + ( substr( $code_string, ( $position - 1 ), 1 ) );
			if ( strtolower( $orientation ) === 'horizontal' ) {

				imagefilledrectangle( $image, $location, 10, $cur_size, $img_height, ( 0 === $position % 2 ? $white : $black ) );

			} else {

				imagefilledrectangle( $image, 0, $location, $img_width, $cur_size, ( 0 === $position % 2 ? $white : $black ) );

			}
			$location = $cur_size;

		}

		$filename_output = '';
		if ( ! empty( $filename ) && ! empty( $file_identifier ) ) {

			$filename_output = $prefix . '-' . $filename . '-' . $file_identifier;

		} elseif ( ! empty( $prefix ) ) {

			$filename_output = $prefix . '-' . $text;

		} else {

			$filename_output = $text;

		}

		imagepng( $image, $this->config->barcode_path . $filename_output . '.png' );

		if ( 'pngjpg' === $this->barcode_output_method ) {

			imagejpeg( $image, $this->config->barcode_path . $filename_output . '.jpg' );

		}

	}

	/**
	 * Converts png image into jpg image
	 *
	 * @param string $original_file png file to convert.
	 * @param string $output_file jpg output path.
	 * @param int    $quality output quality.
	 */
	private function png2jpg( $original_file, $output_file, $quality = 100 ) {

		$image = imagecreatefrompng( $original_file );
		imagejpeg( $image, $output_file, $quality );
		imagedestroy( $image );

	}

}
