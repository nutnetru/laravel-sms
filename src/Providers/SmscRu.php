<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;

/**
 * Class SmscRu
 * @package Nutnet\LaravelSms\Providers
 */
class SmscRu implements Provider
{
    const BASE_URL = 'https://smsc.ru/sys/send.php';
    const PHONE_DELIMITER = ';';

    /**
     * @var mixed|null
     */
    private $login;

    /**
     * @var mixed|null
     */
    private $password;

    /**
     * SmscRu constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->login = $options['login'] ?? null;
        $this->password = $options['password'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function send($phone, $text, array $options = []) : bool
    {
        return $this->sendBatch([$phone], $text, $options);
    }

    /**
     * @inheritdoc
     */
    public function sendBatch(array $phones, $message, array $options = []) : bool
    {
        $response = $this->sendSms($phones, $message, $options);

        if (!$response) {
            return false;
        }

        return !isset($response['error']);
    }

    /**
     * @param array $phones
     * @param $message
     * @param array $additionalParams
     * @return mixed
     */
    private function sendSms(array $phones, $message, array $additionalParams = [])
    {
        $httpQuery = array_merge(
            [
                'login' => $this->login,
                'psw' => md5($this->password),
                'phones' => implode(self::PHONE_DELIMITER, $phones),
                'mes' => mb_convert_encoding($message, 'Windows-1251'),
                'fmt' => 3
            ],
            $additionalParams
        );

        $url = self::BASE_URL.'?'.http_build_query($httpQuery);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response;
    }
}
