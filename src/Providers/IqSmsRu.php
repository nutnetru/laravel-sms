<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.04.18
 */

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\Exceptions\SmsSendingFailedException;
use Illuminate\Support\Arr;
use Nutnet\LaravelSms\Helpers\CurlHelper;

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

    /**
     * @param array{login?: ?string, password?: ?string}&array<array-key, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->validateOptions($options);

        $login = Arr::get($options, 'login');
        $password = Arr::get($options, 'password');

        if (!is_string($login) || !is_string($password)) {
            throw new \InvalidArgumentException('Login and Password must be strings');
        }

        $this->apiLogin = $login;
        $this->apiPassword = $password;
    }

    /**
     * @param array{client_id?: ?string}&array<array-key, mixed> $options
     * @throws SmsSendingFailedException
     */
    public function send(string $phone, string $message, array $options = []): bool
    {
        $result = $this->sendRequest('send', [
            'messages' => array_merge(
                Arr::except($options, 'client_id'),
                [
                    'clientId' => Arr::get($options, 'client_id', '1'),
                    'phone' => $phone,
                    'text' => $message,
                ]
            ),
        ]);

        if ($result['status'] != self::STATUS_OK) {
            throw new SmsSendingFailedException("Failed to send request");
        }

        if (array_key_exists('messages', $result)) {
            foreach ($result['messages'] as $message) {
                if ($message['status'] != self::STATUS_ACCEPTED) {
                    throw new SmsSendingFailedException("Failed to send sms with status: " . $message['status']);
                }
            }
        }

        return true;
    }

    /**
     * @param array{client_id?: ?string}&array<array-key, mixed> $options
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
     * @param ?array<array-key, mixed> $params
     * @throws SmsSendingFailedException
     * @return array{status: string, messages?: list<array{status: string}>}
     */
    private function sendRequest(string $uri, ?array $params = null): array
    {
        $client = CurlHelper::init($this->getUrl($uri));

        curl_setopt_array($client, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array('Host: ' . parse_url(self::API_URL, PHP_URL_HOST)),
            CURLOPT_POSTFIELDS => $this->makePacket($params),
        ));

        try {
            $response = CurlHelper::execJsonArray($client);
        } catch (\JsonException $e) {
            throw new SmsSendingFailedException('Response body is not valid json: ' . $e->getMessage());
        } finally {
            CurlHelper::close($client);
        }

        if (!array_key_exists('status', $response)) {
            throw new SmsSendingFailedException('Response body does not contain required "status" field');
        }

        return $response;
    }

    private function getUrl(string $uri): string
    {
        return self::API_URL . '/' . $uri . '/';
    }

    /**
     * @param ?array<array-key, mixed> $params
     */
    private function makePacket(?array $params = null): string
    {
        $params = $params ?: [];
        $params['login'] = $this->apiLogin;
        $params['password'] = $this->apiPassword;

        return (string)json_encode(array_filter($params));
    }

    /**
     * @param array{login?: ?string, password?: ?string}&array<array-key, mixed> $options
     */
    private function validateOptions(array $options): void
    {
        if (empty($options['login']) || empty($options['password'])) {
            throw new \InvalidArgumentException('Login and password is required.');
        }
    }
}
