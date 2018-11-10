<?php
/**
 * Plugin Name: Vales WP-CAPTCHA
 * Description: Silence is golden.
 * Version: 1.0.0
 * Author: Vales, Inc.
 * Author URI: https://valesdigital.com/
 */


require( __DIR__ . '/class.captcha.php' );


class Vales_WP_CAPTCHA {

	const PLUGIN_NAME = 'Vales WP-CAPTCHA';
	const PLUGIN_SLUG = 'vales_wp_captcha';

	public static function init() {
		add_action( 'login_enqueue_scripts'                , array( get_class(), 'login_enqueue_scripts' ) );
		add_action( 'login_form'                           , array( get_class(), 'login_form' ) );
		add_action( 'authenticate'                         , array( get_class(), 'authenticate' ), 50, 3 );
		add_action(        'wp_ajax_' . static::PLUGIN_SLUG, array( get_class(), 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_' . static::PLUGIN_SLUG, array( get_class(), 'ajax_handler' ) );
	}

	public static function login_enqueue_scripts() {
		?>
			<style type="text/css">
				.captcha-wrap {
					display: -webkit-box;
					display: -ms-flexbox;
					display: flex;
					-ms-flex-wrap: wrap;
					    flex-wrap: wrap;
					margin-left: -1em !important;
					margin-right: -1em !important;
				}
				.captcha-input,
				.captcha-image {
					display: block;
					box-sizing: border-box;
					-webkit-box-flex: 0;
					        -ms-flex: 0 0 50%;
					            flex: 0 0 50%;
					max-width: 50%;
					padding-left: 1em !important;
					padding-right: 1em !important;
					text-align: right;
				}
				.captcha-image img {
					display: block;
					max-width: 100%;
					height: auto;
					margin-top: 2px;
				}
			</style>
		<?
	}

	public static function login_form() {
		$captcha = new ValesCaptcha( [
			'hash_salt'    => NONCE_SALT,
			'image_width'  => 122 * 2,
			'image_height' => 36 * 2,
		] );
		$result = $captcha->generate();
		?>
			<p>
				<label for="captcha">CAPTCHA<br />
					<span class="captcha-wrap">
						<span class="captcha-input">
							<input type="text" name="captcha" class="input" value="" size="20" />
						</span>
						<span class="captcha-image">
							<img src="<?= esc_attr( $result['image_inline'] ) ?>" />
						</span>
					</span>
					<input type="hidden" name="captcha_hash" value="<?= esc_attr( $result['hash'] ) ?>" />
				</label>
			</p>
		<?
	}

	public static function authenticate( $user, $username, $password ) {
		if ( ! empty( $username ) && ! empty( $password ) ) {
			$ok = false;
			$captcha = new ValesCaptcha( [
				'hash_salt' => NONCE_SALT,
			] );
			if ( array_key_exists( 'captcha', $_POST ) && array_key_exists( 'captcha_hash', $_POST ) ) {
				if ( $captcha->check( $_POST['captcha'], $_POST['captcha_hash'] ) ) {
					$ok = true;
				}
			}
			if ( true !== $ok ) {
				$user = new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid validation code.' ) );
			}
		}
		return $user;
	}

	public static function ajax_handler() {}

}

Vales_WP_CAPTCHA::init();

