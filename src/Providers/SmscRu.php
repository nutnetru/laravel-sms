<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\Helpers\CurlHelper;

class SmscRu implements Provider
{
	public const PHONE_DELIMITER = ';';
    private const BASE_URL = 'https://smsc.ru/sys/send.php';

    private ?string $login;

    private ?string $password;

	/**
	 * @param array{login?: ?string, password?: ?string} $options
	 */
    public function __construct(array $options)
    {
        $this->login = $options['login'] ?? null;
        $this->password = $options['password'] ?? null;
    }

	/**
	 * @param array<array-key, mixed> $options
	 */
    public function send(string $phone, string $text, array $options = []) : bool
    {
        return $this->sendBatch([$phone], $text, $options);
    }

	/**
	 * @param array<array-key, mixed> $options
	 */
    public function sendBatch(array $phones, string $message, array $options = []) : bool
    {
        $response = $this->doRequest(array_merge(
            [
                'login' => $this->login,
                'psw' => $this->password,
                'phones' => implode(self::PHONE_DELIMITER, $phones),
                'mes' => mb_convert_encoding($message, 'Windows-1251'),
                'fmt' => 3
            ],
            $options
        ));

        if (!is_array($response)) {
            return true == $response;
        }

        return !isset($response['error']);
    }

	/**
	 * @param array<array-key, mixed> $httpQuery
	 * @return array<array-key, mixed>|bool
	 */
    protected function doRequest(array $httpQuery): array|bool
    {
        $ch = CurlHelper::init(self::BASE_URL . '?' . http_build_query($httpQuery));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$response = CurlHelper::execJsonArray($ch);
		CurlHelper::close($ch);

        return $response;
    }
}
