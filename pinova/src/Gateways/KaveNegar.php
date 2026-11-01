<?php

namespace Pinova\Gateways;

use Exception;
use Pinova\Helpers\Curl;

class KaveNegar extends BaseGateway {

	protected string $name = 'کاوه نگار';

	protected string $url = 'kavenegar.com';

	public string $video_url = 'https://aparat.com/v/dnr7r4x';

	public function send( string $mobile, string $message ): bool {

		if ( empty( $this->get_option( 'api_key' ) ) ) {
			throw new Exception( 'API Key را در بخش تنظیمات وبسرویس تعریف کنید.' );
		}

		if ( $this->is_pattern( $message ) ) {

			$pattern = $this->parse_pattern( $message );

			$token_params = '';

			foreach ( $pattern['vars'] as $key => $value ) {
				$token_params .= '&' . $key . '=' . rawurlencode( $value );
			}

			$url = sprintf(
				'https://api.kavenegar.com/v1/%s/verify/lookup.json?receptor=%s&template=%s%s',
				$this->get_option( 'api_key' ),
				rawurlencode( $mobile ),
				rawurlencode( $pattern['code'] ),
				$token_params
			);

		} else {

			$query_params = [
				'sender'   => $this->get_option( 'sender' ),
				'receptor' => $mobile,
				'message'  => $message,
			];

			$url = sprintf(
				'https://api.kavenegar.com/v1/%s/sms/send.json?%s',
				$this->get_option( 'api_key' ),
				http_build_query( $query_params )
			);
		}

		$response = Curl::post( $url );

		$status = $response['return']['status'] ?? 0;

		if ( $status == 200 ) {
			return true;
		}

		if ( isset( $response['return']['message'] ) ) {
			throw new Exception( $response['return']['message'], $status );
		}

		throw new Exception( 'خطای ناشناخته در ارسال به کاوه‌نگار رخ داده است.' );
	}

	public function is_enable(): bool {
		return ! empty( $this->get_option( 'api_key' ) );
	}

	public function options(): array {
		return [
			[
				'label'       => 'API Key',
				'key'         => 'api_key',
				'description' => 'API Key را از منو <a href="https://console.kavenegar.com/setting/security?tab=api-key" target="_blank">تنظیمات » تنظیمات امنیتی » API Key</a> دریافت کنید.',
				'field_class' => 'ltr',
			],
			[
				'label'       => 'شماره فرستنده',
				'key'         => 'sender',
				'description' => 'شماره مبدا/فرستنده را از منو <a href="https://console.kavenegar.com/lines" target="_blank">خطوط » مدیریت خطوط</a> دریافت کنید.',
				'field_class' => 'ltr',
			],
		];
	}
}