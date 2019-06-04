<?php
namespace Nutnet\LaravelSms;

use Nutnet\LaravelSms\Contracts\Provider;

/**
 * Class SmsSender
 * @package App\Services\Sms
 */
class SmsSender
{
    /**
     * @var Provider
     */
    private $bridge;

    /**
     * SmsSender constructor.
     * @param Provider $bridge
     */
    public function __construct(Provider $bridge)
    {
        $this->bridge = $bridge;
    }

    /**
     * @param $phone
     * @param $message
     * @param array $options
     */
    public function send($phone, $message, array $options = [])
    {
        $this->bridge->send($this->preparePhone($phone), $message, $options);
    }

    /**
     * @param array $phones
     * @param $message
     * @param array $options
     */
    public function sendBatch(array $phones, $message, array $options = [])
    {
        $this->bridge->sendBatch(
            array_map([$this, 'preparePhone'], $phones),
            $message,
            $options
        );
    }

    private function preparePhone($phone)
    {
        return preg_replace('/[^\d]+/', '', $phone);
    }
}
