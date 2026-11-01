<?php

namespace Pinova;

defined( 'ABSPATH' ) || exit;

class Helper {

	/**
	 * @param string $url
	 *
	 * @return no-return
	 */
	public static function redirect_to( string $url ) {
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * @param string $url
	 *
	 * @return no-return
	 */
	public static function js_redirect_to( string $url ) {
		?>
		<script>
            window.location.replace('<?php echo esc_url( $url ); ?>');
		</script>
		<?php
		exit;
	}

	/**
	 * @param string $dateTime
	 * @param string $format
	 *
	 * @return false|string
	 */
	public static function date( string $dateTime, string $format = 'Y/m/d H:i:s' ) {
		return wp_date( $format, strtotime( $dateTime ) );
	}

	public static function is_same_domain( string $first_url, string $second_url ): bool {
		$first_host  = wp_parse_url( $first_url, PHP_URL_HOST );
		$second_host = wp_parse_url( $second_url, PHP_URL_HOST );

		if ( ! is_string( $first_host ) || ! is_string( $second_host ) ) {
			return false;
		}

		return strcasecmp( $first_host, $second_host ) === 0;
	}

	public static function get_login_back_url( ?string $back_url = null ): string {

		$fallback_url = current_user_can( 'manage_options' ) ? admin_url() : home_url();

		if ( ! filter_var( $back_url, FILTER_VALIDATE_URL ) ) {
			$back_url = sanitize_url( $_POST['back_url'] ?? $_GET['back_url'] ?? '' );
		}

		if ( ! filter_var( $back_url, FILTER_VALIDATE_URL ) ) {
			$back_url = $fallback_url;
		}

		$back_url = apply_filters( 'pinova/login_back_url', sanitize_url( $back_url ) );

		return self::is_same_domain( $back_url, home_url() ) ? $back_url : $fallback_url;
	}

	public static function get_logout_back_url( ?string $back_url = null ): string {

		$fallback_url = home_url();

		if ( ! filter_var( $back_url, FILTER_VALIDATE_URL ) ) {
			$back_url = sanitize_url( $_POST['back_url'] ?? $_GET['back_url'] ?? '' );
		}

		if ( ! filter_var( $back_url, FILTER_VALIDATE_URL ) ) {
			$back_url = $fallback_url;
		}

		$back_url = apply_filters( 'pinova/logout_back_url', sanitize_url( $back_url ) );

		return self::is_same_domain( $back_url, home_url() ) ? $back_url : $fallback_url;
	}

}
