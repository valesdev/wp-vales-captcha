<?

class ValesCaptcha {

	private $params = [];

	public function __construct( $params = [] ) {
		$this->params = array_merge( [
			'image_width'      => 120,
			'image_height'     => 40,
			'length'           => 4,
			'dots'             => 10,
			'lines'            => 10,
			'background_color' => [ '0xEEFFFF', '0xFFEEFF', '0xFFFFEE' ],
			'text_color'       => [ '0x142864', '0x146416', '0x641414' ],
			'noise_color'      => [ '0x645f14', '0x1f1464', '0x641452' ],
			'font'             => __DIR__ . '/fonts/monofont.ttf',
			'hash_salt'        => '_PUT_YOUR_UNIQUE_PHRASE_HERE_',
		], $params );
	}

	public function generate() {

		$font_size = $this->params['image_height'] * 0.75;
		$image = @imagecreate( $this->params['image_width'], $this->params['image_height'] );

		shuffle( $this->params['background_color'] );
		$background_color = $this->hexrgb( $this->params['background_color'][0] );
		$background_color_obj = imagecolorallocate( $image, $background_color['r'], $background_color['g'], $background_color['b'] );

		shuffle( $this->params['text_color'] );
		$text_color = $this->hexrgb( $this->params['text_color'][0] );
		$text_color_obj = imagecolorallocate( $image, $text_color['r'], $text_color['g'], $text_color['b'] );

		shuffle( $this->params['noise_color'] );
		$noise_color = $this->hexrgb( $this->params['noise_color'][0] );
		$noise_color_obj = imagecolorallocate( $image, $noise_color['r'], $noise_color['g'], $noise_color['b'] );

		for ( $i = 1; $i <= $this->params['dots']; $i ++ ) {
			imagefilledellipse( $image,
				mt_rand( 0, $this->params['image_width'] ),
				mt_rand( 0, $this->params['image_height'] ),
				2,
				3,
				$noise_color_obj
			);
		}

		for ( $i = 1; $i <= $this->params['lines']; $i ++ ) {
			imageline( $image,
				mt_rand( 0, $this->params['image_width'] ),
				mt_rand( 0, $this->params['image_height'] ),
				mt_rand( 0, $this->params['image_width'] ),
				mt_rand( 0, $this->params['image_height'] ),
				$noise_color_obj
			);
		}

		$code = '';

		$possible_letters = '1234567890abcdefghijklmnopqrstuvwxyz';
		for ( $i = 1; $i <= $this->params['length']; $i ++ ) {
			$code .= substr( $possible_letters, mt_rand( 0, strlen( $possible_letters ) - 1 ), 1 );
		}

		for ( $i = 0; $i < strlen( $code ); $i ++ ) {
			imagettftext( $image,
				$font_size,
				mt_rand( -30, 30 ),
				$i * $this->params['image_width'] / $this->params['length'] + ( $this->params['image_width'] / $this->params['length'] ) * 0.25,
				$this->params['image_height'] * 0.75,
				$text_color_obj,
				$this->params['font'],
				substr( $code, $i, 1 )
			);
		}

		ob_start();
		imagegif( $image );
		imagedestroy( $image );
		$bin = ob_get_contents();
		ob_end_clean();

		return [
			'code'         => $code,
			'hash'         => $this->hash( $code ),
			'image_binary' => $bin,
			'image_inline' => sprintf( 'data:image/gif;base64,%s', base64_encode( $bin ) ),
		];

	}

	public function hash( $code ) {
		return md5( md5( $code ) . $this->params['hash_salt'] );
	}

	public function check( $code, $hash ) {
		return $hash === $this->hash( $code );
	}

	private function hexrgb( $hexstr ) {
		$int = hexdec( $hexstr );
		return [
			'r' => 0xFF & ( $int >> 0x10 ),
			'g' => 0xFF & ( $int >> 0x8 ),
			'b' => 0xFF & $int,
		];
	}

}

