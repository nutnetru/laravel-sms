<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 18.05.17
 */

namespace Nutnet\LaravelSms\Providers;

use Illuminate\Support\Arr;
use Nutnet\LaravelSms\Contracts\Provider;
use Zelenin\SmsRu as SmsRuApi;

class SmsRu implements Provider
{
    public const CODE_OK = 100;

	public const AUTH_STANDARD = 'standard';
    public const AUTH_SECURED = 'secured';
    public const AUTH_API_ID = 'api_id';

	private const AUTH_TYPES = [
		self::AUTH_STANDARD => 'makeAuthStandard',
		self::AUTH_SECURED => 'makeAuthSecured',
		self::AUTH_API_ID => 'makeAuthApiId'
	];

    private ?SmsRuApi\Api $api = null;

	private SmsRuApi\Client\ClientInterface $httpClient;

    public function __construct(private array $options, SmsRuApi\Client\ClientInterface $httpClient = null)
    {
		$this->httpClient = $httpClient !== null ? $httpClient : new SmsRuApi\Client\Client();
    }

    /**
     * @throws SmsRuApi\Exception\Exception
     */
    public function send(string $phone, string $text, array $options = []) : bool
    {
        $response = $this->getApi()->smsSend(
            $this->makeMessage($phone, $text, $options)
        );

        return $response->code == static::CODE_OK;
    }

    /**
	 * @param array<array-key, string> $phones
     * @throws SmsRuApi\Exception\Exception
     */
    public function sendBatch(array $phones, string $message, array $options = []) : bool
    {
        $smsList = array_map(function ($phone) use ($message, $options) {
            return $this->makeMessage($phone, $message, $options);
        }, $phones);
        $response = $this->getApi()->smsSend(new SmsRuApi\Entity\SmsPool($smsList));

        return $response->code == static::CODE_OK;
    }

    public function getApi(): SmsRuApi\Api
    {
        if ($this->api === null) {
            return $this->api = new SmsRuApi\Api($this->getAuth(), $this->httpClient);
        }

        return $this->api;
    }

    private function getAuth(): SmsRuApi\Auth\AuthInterface
    {
        $authType = Arr::get($this->options, 'auth_type', self::AUTH_STANDARD);

        if (!array_key_exists($authType, self::AUTH_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Unsupported auth type: %s', $authType));
        }

        $authBuilder = self::AUTH_TYPES[$authType];

        return $this->$authBuilder();
    }

    private function makeMessage(string $phone, string $text, array $options = []): SmsRuApi\Entity\Sms
    {
        $message = new SmsRuApi\Entity\Sms($phone, $text);

        // set message options, @see available on https://sms.ru/api/send
        foreach ($options as $optionName => $optionValue) {
            if (property_exists($message, $optionName)) {
                $message->$optionName = $optionValue;
            }
        }

        return $message;
    }

    private function makeAuthStandard(): SmsRuApi\Auth\LoginPasswordAuth
    {
        return new SmsRuApi\Auth\LoginPasswordAuth(
            Arr::get($this->options, 'login'),
            Arr::get($this->options, 'password'),
            Arr::get($this->options, 'partner_id'),
        );
    }

    private function makeAuthSecured(): SmsRuApi\Auth\LoginPasswordSecureAuth
    {
        return new SmsRuApi\Auth\LoginPasswordSecureAuth(
            Arr::get($this->options, 'login'),
            Arr::get($this->options, 'password'),
            Arr::get($this->options, 'api_id'),
            null,
            Arr::get($this->options, 'partner_id'),
        );
    }

    private function makeAuthApiId(): SmsRuApi\Auth\ApiIdAuth
    {
        return new SmsRuApi\Auth\ApiIdAuth(
            Arr::get($this->options, 'api_id'),
            Arr::get($this->options, 'partner_id'),
        );
    }
}
