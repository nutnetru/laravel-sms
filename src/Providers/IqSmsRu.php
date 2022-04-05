<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.04.18
 */

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\Exceptions\SmsSendingFailedException;
use Illuminate\Support\Arr;

/**
 * @link https://iqsms.ru
 */
class IqSmsRu implements Provider
{
    private const STATUS_OK = 'ok';
	private const STATUS_ACCEPTED = 'accepted';

	private const API_URL = 'http://json.gate.iqsms.ru';
	private const PACKET_SIZE = 20;

    private string $apiLogin;

    private string $apiPassword;

    public function __construct(array $options)
    {
        $this->validateOptions($options);
        
        $this->apiLogin = Arr::get($options, 'login');
        $this->apiPassword = Arr::get($options, 'password');
    }

    /**
     * @throws SmsSendingFailedException
     */
    public function send(string $phone, string $message, array $options = []): bool
    {
        $result = $this->sendRequest('send', [
            'messages' => array_merge(
                Arr::except($options, 'client_id'),
                [
                    'clientId' => Arr::get($options, 'client_id', "1"),
                    'phone' => $phone,
                    'text' => $message,
                ]
            ),
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
     * @throws SmsSendingFailedException
     */
    public function sendBatch(array $phones, string $message, array $options = []): bool
    {
        $params = [
            'messages' => []
        ];
        $clientId = Arr::get($options, 'client_id', '1');

        foreach (array_chunk($phones, self::PACKET_SIZE) as $phonesPacket) {
            foreach ($phonesPacket as $phone) {
                $params['messages'][] = array_merge(
                    Arr::except($options, 'client_id'),
                    [
                        'clientId' => $clientId,
                        'phone' => $phone,
                        'text' => $message,
                    ]
                );
            }
        }

        $response = $this->sendRequest('send', $params);

        return $response['status'] == self::STATUS_OK;
    }

    /**
     * @throws SmsSendingFailedException
     */
    private function sendRequest(string $uri, ?array $params = null): array|bool
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

    private function getUrl(string $uri): string
    {
        return self::API_URL . '/' . $uri . '/';
    }

    private function makePacket(?array $params = null): string
    {
        $params = $params ?: [];
        $params['login'] = $this->apiLogin;
        $params['password'] = $this->apiPassword;

        return json_encode(array_filter($params));
    }

    private function validateOptions(array $options): void
    {
        if (empty($options['login']) || empty($options['password'])) {
            throw new \InvalidArgumentException('Login and password is required.');
        }
    }
}
