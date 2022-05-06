<?php

namespace Nutnet\LaravelSms\Helpers;

use CurlHandle;

final class CurlHelper
{
	public static function init(string $url): CurlHandle
	{
		$ch = curl_init($url);

		if ($ch === false) {
			throw new \RuntimeException("curl_init failed on url $url");
		}

		return $ch;
	}

	public static function execString(CurlHandle $handle): string
	{
		$response = curl_exec($handle);

		if (!is_string($response)) {
			throw new \RuntimeException(sprintf('Curl request failed: %s', curl_error($handle)));
		}

		return $response;
	}

	/**
	 * @return array<array-key, mixed>
	 */
	public static function execJsonArray(CurlHandle $handle): array
	{
		return (array)json_decode(self::execString($handle), true, 512, JSON_THROW_ON_ERROR);
	}

	public static function close(CurlHandle $handle): void
	{
		curl_close($handle);
	}
}