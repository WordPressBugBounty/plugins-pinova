<?php

namespace Pinova\Helpers;

use Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

class IP {

	public static function get(): ?string {
		$trusted_proxies = apply_filters( 'pinova/trusted_proxies', [] );

		Request::setTrustedProxies(
			$trusted_proxies,
			Request::HEADER_X_FORWARDED_FOR | Request::HEADER_FORWARDED
		);

		$request = Request::createFromGlobals();

		try {
			$ip = $request->getClientIp();
		} catch ( ConflictingHeadersException $e ) {
			return null;
		}

		if ( ! self::is_valid( $ip ) || IpUtils::checkIp( $ip, $trusted_proxies ) ) {
			return null;
		}

		return $ip;
	}

	public static function is_valid( ?string $ip = '' ): bool {
		return boolval( filter_var( $ip, FILTER_VALIDATE_IP ) );
	}
}
