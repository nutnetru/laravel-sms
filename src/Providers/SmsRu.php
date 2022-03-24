<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 18.05.17
 */

namespace Nutnet\LaravelSms\Providers;

use Illuminate\Support\Arr;
use Nutnet\LaravelSms\Contracts\Provider;
use Zelenin\SmsRu as SmsRuApi;

/**
 * Class SmsRu
 * @package Nutnet\LaravelSms\Providers
 */
class SmsRu implements Provider
{
    const CODE_OK = 100;

    const AUTH_STANDARD = 'standard';
    const AUTH_SECURED = 'secured';
    const AUTH_API_ID = 'api_id';

    /**
     * @var SmsRuApi\Api
     */
    private $api;

	/**
	 * @var SmsRuApi\Client\ClientInterface
	 */
	private $httpClient;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $authTypes = [
        self::AUTH_STANDARD => 'makeAuthStandard',
        self::AUTH_SECURED => 'makeAuthSecured',
        self::AUTH_API_ID => 'makeAuthApiId'
    ];

    public function __construct(array $options, SmsRuApi\Client\ClientInterface $httpClient = null)
    {
        $this->options = $options;
		$this->httpClient = $httpClient ?? new SmsRuApi\Client\Client();
    }

    /**
     * @param $phone
     * @param $text
     * @param array $options
     * @return bool
     * @throws SmsRuApi\Exception\Exception
     */
    public function send($phone, $text, array $options = []) : bool
    {
        $response = $this->getApi()->smsSend(
            $this->makeMessage($phone, $text, $options)
        );

        return $response->code == self::CODE_OK;
    }

    /**
     * @param array $phones
     * @param $message
     * @param array $options
     * @return bool
     * @throws SmsRuApi\Exception\Exception
     */
    public function sendBatch(array $phones, $message, array $options = []) : bool
    {
        $smsList = array_map(function ($phone) use ($message, $options) {
            return $this->makeMessage($phone, $message, $options);
        }, $phones);
        $response = $this->getApi()->smsSend(new SmsRuApi\Entity\SmsPool($smsList));

        return $response->code == self::CODE_OK;
    }

    /**
     * @return SmsRuApi\Api
     */
    public function getApi()
    {
        if (!$this->api) {
            return $this->api = new SmsRuApi\Api($this->getAuth(), $this->httpClient);
        }

        return $this->api;
    }

    /**
     * @return SmsRuApi\Auth\AuthInterface
     */
    private function getAuth()
    {
        $authType = Arr::get($this->options, 'auth_type', self::AUTH_STANDARD);

        if (!array_key_exists($authType, $this->authTypes)) {
            throw new \InvalidArgumentException(sprintf('Unsupported auth type: %s', $authType));
        }

        $authBuilder = $this->authTypes[$authType];

        return $this->$authBuilder();
    }

    /**
     * @param $phone
     * @param $text
     * @param array $options
     * @return SmsRuApi\Entity\Sms
     */
    private function makeMessage($phone, $text, array $options = [])
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

    /**
     * @return SmsRuApi\Auth\LoginPasswordAuth
     */
    private function makeAuthStandard()
    {
        return new SmsRuApi\Auth\LoginPasswordAuth(
            Arr::get($this->options, 'login'),
            Arr::get($this->options, 'password'),
            Arr::get($this->options, 'partner_id')
        );
    }

    /**
     * @return SmsRuApi\Auth\LoginPasswordSecureAuth
     */
    private function makeAuthSecured()
    {
        return new SmsRuApi\Auth\LoginPasswordSecureAuth(
            Arr::get($this->options, 'login'),
            Arr::get($this->options, 'password'),
            Arr::get($this->options, 'api_id'),
            null,
            Arr::get($this->options, 'partner_id')
        );
    }

    /**
     * @return SmsRuApi\Auth\ApiIdAuth
     */
    private function makeAuthApiId()
    {
        return new SmsRuApi\Auth\ApiIdAuth(
            Arr::get($this->options, 'api_id'),
            Arr::get($this->options, 'partner_id')
        );
    }
}
