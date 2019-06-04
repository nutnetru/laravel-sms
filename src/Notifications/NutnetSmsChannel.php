<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 04.06.19
 */

namespace Nutnet\LaravelSms\Notifications;

use Nutnet\LaravelSms\SmsSender;
use Illuminate\Notifications\Notification;

/**
 * Class NutnetSmsChannel
 * @package Nutnet\LaravelSms\Notifications
 */
class NutnetSmsChannel
{
    /**
     * @var SmsSender
     */
    private $sender;

    /**
     * NutnetSmsChannel constructor.
     * @param SmsSender $sender
     */
    public function __construct(SmsSender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        $phone = $notifiable->routeNotificationFor('nutnet_sms');

        if (!$phone) {
            return;
        }

        /** @var NutnetSmsMessage $message */
        $message = $notification->toNutnetSms($notifiable);

        if (!($message instanceof NutnetSmsMessage)) {
            $message = new NutnetSmsMessage($message);
        }

        $this->sender->send($phone, $message->getContent(), $message->getOptions());
    }
}