<?php

namespace Pinova\Gateways;

use Exception;
use Pinova\Helpers\Curl;

class IranPayamak extends BaseGateway {

	protected string $name = 'ایران پیامک (فراز اس ام اس)';

	protected string $url = 'iranpayamak.com';

	public string $video_url = 'https://www.aparat.com/v/odp2177';

	public function send( string $mobile, string $message ): bool {

		if ( empty( $this->get_option( 'api_key' ) ) ) {
			throw new Exception( 'کلید وبسرویس را در بخش تنظیمات وبسرویس تعریف کنید.' );
		}

		if ( empty( $this->get_option( 'sender' ) ) ) {
			throw new Exception( 'شماره فرستنده پیامک تعیین نشده است.' );
		}

		if ( str_starts_with( $mobile, '+98' ) ) {
			$mobile = substr( $mobile, 3 );
		}

		if ( ! str_starts_with( $mobile, '0' ) ) {
			$mobile = '0' . $mobile;
		}

		if ( $this->is_pattern( $message ) ) {

			$pattern = $this->parse_pattern( $message );

			$payload = [
				'code'          => $pattern['code'],
				'attributes'    => $pattern['vars'],
				'line_number'   => $this->get_option( 'sender' ),
				'from'          => $this->get_option( 'sender' ),
				'recipient'     => $mobile,
				'number_format' => 'english',
				'numberFormat'  => 'english',
			];

			$url = 'https://api.iranpayamak.com/ws/v1/sms/pattern';

		} else {

			$payload = [
				'text'          => $message,
				'recipients'    => [ $mobile ],
				'from'          => $this->get_option( 'sender' ),
				'line_number'   => $this->get_option( 'sender' ),
				'number_format' => 'english',
			];

			$url = 'https://api.iranpayamak.com/ws/v1/sms/simple';
		}

		$response = Curl::post(
			$url,
			json_encode( $payload ),
			[
				'Content-Type: application/json',
				'Accept: application/json',
				'Api-Key: ' . $this->get_option( 'api_key' ),
			]
		);

		if ( isset( $response['status'] ) && $response['status'] === 'success' ) {
			return true;
		}

		if ( isset( $response['messages'] ) ) {

			$message = is_array( $response['messages'] )
				? implode( ', ', $response['messages'] )
				: $response['messages'];

			throw new Exception( $message );
		}

		throw new Exception( 'خطای ناشناخته در ارسال به فراز اس ام اس رخ داده است.' );
	}

	public function is_enable(): bool {
		return ! empty( $this->get_option( 'api_key' ) );
	}

	public function options(): array {
		return [
			[
				'label'       => 'کلید دسترسی',
				'key'         => 'api_key',
				'description' => 'کلید دسترسی را از منو <a href="https://panel.iranpayamak.com/web-service/access-keys" target="_blank">وبسرویس » کلید‌های دسترسی</a> دریافت کنید.',
				'field_class' => 'ltr',
			],
			[
				'label'       => 'شماره فرستنده',
				'key'         => 'sender',
				'description' => 'شماره مبدا/فرستنده را از منو <a href="https://panel.iranpayamak.com/send/simple" target="_blank">ارسال پیامک » ارسال پیامک ساده</a> دریافت کنید.',
				'field_class' => 'ltr',
			],
		];
	}
}