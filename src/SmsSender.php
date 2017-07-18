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
     */
    public function send($phone, $message)
    {
        $this->bridge->send($this->preparePhone($phone), $message);
    }

    /**
     * @param array $phones
     * @param $message
     */
    public function sendBatch(array $phones, $message)
    {
        $this->bridge->send(
            array_map([$this, 'preparePhone'], $phones),
            $message
        );
    }

    private function preparePhone($phone)
    {
        return preg_replace('/[^\d]+/', '', $phone);
    }
}
