<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Providers;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\Providers\Smpp\SmppSender;

class Smpp implements Provider
{
    public function __construct(private SmppSender $smpp)
    {
    }

    /**
     * @param array<array-key, mixed> $options
     */
    public function send(string $phone, string $text, array $options = []) : bool
    {
        $this->smpp->sendOne((int)$phone, $text);

        return true;
    }

    /**
     * @param array<array-key, mixed> $options
     */
    public function sendBatch(array $phones, string $message, array $options = []) : bool
    {
        $this->smpp->sendBulk($phones, $message);

        return true;
    }
}
