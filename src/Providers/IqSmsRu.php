<?php

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\Exceptions\SmsSendingFailedException;
use Illuminate\Support\Arr;

/**
 * @link https://iqsms.ru
 * Class IqSmsRu
 * @package Nutnet\LaravelSms\Providers
 */
class IqSmsRu implements Provider
{
    const ERROR_EMPTY_RESPONSE = 'errorEmptyResponse';

    const STATUS_OK = 'ok';
    const STATUS_ACCEPTED = 'accepted';

    const API_URL = 'http://json.gate.iqsms.ru';
    const PACKET_SIZE = 20;

    /**
     * @var string
     */
    private $apiLogin;

    /**
     * @var string
     */
    private $apiPassword;

    /**
     * IqSmsRu constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->validateOptions($options);
        
        $this->apiLogin = Arr::get($options, 'login');
        $this->apiPassword = Arr::get($options, 'password');
    }

    /**
     * @param $phone
     * @param $message
     * @param array $options
     * @return bool
     * @throws SmsSendingFailedException
     */
    public function send($phone, $message, array $options = []): bool
    {
        $result = $this->sendRequest('send', [
            'messages' => [
                'clientId' => Arr::get($options, 'client_id', "1"),
                'phone' => $phone,
                'text' => $message,
            ],
        ]);

        if ($result['status'] != self::STATUS_OK) {
            throw new SmsSendingFailedException("Failed to send request");
        }

        foreach ($result['messages'] as $message) {
            if ($message['status'] != self::STATUS_ACCEPTED) {
                throw new SmsSendingFailedException("Failed to send sms with status: " . $message['status']);
            }
        }

        return true;
    }

    /**
     * @param array $phones
     * @param $message
     * @param array $options
     * @return bool
     * @throws SmsSendingFailedException
     */
    public function sendBatch(array $phones, $message, array $options = []): bool
    {
        $params = [
            'messages' => []
        ];
        $clientId = Arr::get($options, 'client_id', '1');

        foreach (array_chunk($phones, self::PACKET_SIZE) as $phonesPacket) {
            foreach ($phonesPacket as $phone) {
                $params['messages'][] = array(
                    'clientId' => $clientId,
                    'phone' => $phone,
                    'text' => $message,
                );
            }
        }

        $response = $this->sendRequest('send', $params);

        return $response['status'] == self::STATUS_OK;
    }

    /**
     * @param $uri
     * @param null $params
     * @return mixed
     * @throws SmsSendingFailedException
     */
    private function sendRequest($uri, $params = null)
    {
        $client = curl_init($this->getUrl($uri));
        curl_setopt_array($client, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Host: ' . parse_url(self::API_URL, PHP_URL_HOST)),
            CURLOPT_POSTFIELDS => $this->makePacket($params),
        ));

        $body = curl_exec($client);
        curl_close($client);

        if (empty($body)) {
            throw new SmsSendingFailedException('IQSms sends empty response.');
        }

        $decodedBody = json_decode($body, true);

        if (is_null($decodedBody)) {
            throw new SmsSendingFailedException('Response body is not valid json.');
        }

        return $decodedBody;
    }

    /**
     * @param $uri
     * @return string
     */
    private function getUrl($uri)
    {
        return self::API_URL . '/' . $uri . '/';
    }

    /**
     * @param null $params
     * @return false|string
     */
    private function makePacket($params = null)
    {
        $params = $params ?: [];
        $params['login'] = $this->apiLogin;
        $params['password'] = $this->apiPassword;

        return json_encode(array_filter($params));
    }

    /**
     * @param array $options
     */
    private function validateOptions(array $options)
    {
        if (empty($options['login']) || empty($options['password'])) {
            throw new \InvalidArgumentException('Login and password is required.');
        }
    }
}
