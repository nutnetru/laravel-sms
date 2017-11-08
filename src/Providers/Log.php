<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 18.05.17
 */

namespace Nutnet\LaravelSms\Providers;

use Illuminate\Log\Writer;
use Nutnet\LaravelSms\Contracts\Provider;

/**
 * Class Log
 * @package Nutnet\LaravelSms\Providers
 */
class Log implements Provider
{
    /**
     * @var Writer
     */
    private $logWriter;

    /**
     * LogDriver constructor.
     * @param Writer $logWriter
     */
    public function __construct(Writer $logWriter)
    {
        $this->logWriter = $logWriter;
    }

    /**
     * Send single sms
     * @param $phone
     * @param $text
     * @return mixed
     */
    public function send($phone, $text, array $options = []) : bool
    {
        $this->logWriter->debug(sprintf(
            'Sms is sent to %s: "%s"',
            $phone,
            $text
        ));

        return true;
    }

    /**
     * @param array $phones
     * @param $message
     * @return bool
     */
    public function sendBatch(array $phones, $message, array $options = []) : bool
    {
        foreach ($phones as $phone) {
            $this->send($phone, $message);
        }

        return true;
    }
}
