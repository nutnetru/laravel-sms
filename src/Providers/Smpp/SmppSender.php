<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Providers\Smpp;

use SMPP;
use SmppAddress;
use LaravelSmpp\SmppService;

class SmppSender extends SmppService
{
    /**
     * @inheritdoc
     */
    protected function sendSms(SmppAddress $sender, $recipient, $message)
    {
        $message = mb_convert_encoding($message, 'UCS-2', 'UTF-8');

        return $this->smpp->sendSMS(
            $sender,
            $this->getRecipient($recipient),
            $message,
            null,
            SMPP::DATA_CODING_UCS2
        );
    }
}
