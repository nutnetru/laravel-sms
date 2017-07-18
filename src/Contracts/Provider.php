<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Contracts;

/**
 * Interface BridgeInterface
 * @package App\Services\Sms\Bridges
 */
interface Provider
{
    /**
     * @param $phone
     * @param $message
     * @return bool
     */
    public function send($phone, $message) : bool;

    /**
     * @param array $phones
     * @param $message
     * @return bool
     */
    public function sendBatch(array $phones, $message) : bool;
}
