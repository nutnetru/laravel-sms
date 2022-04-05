<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 04.06.19
 */

namespace Nutnet\LaravelSms\Notifications;

use Nutnet\LaravelSms\SmsSender;
use Illuminate\Notifications\Notification;

class NutnetSmsChannel
{
    public function __construct(private SmsSender $sender)
    {
    }

    public function send($notifiable, Notification $notification): void
    {
        $phone = $notifiable->routeNotificationFor('nutnet_sms');

        if (!$phone) {
            return;
        }

		if (!method_exists($notification, 'toNutnetSms')) {
			throw new \InvalidArgumentException('$notification must implement toNutnetSms method');
		}

        $message = $notification->toNutnetSms($notifiable);

        if (!($message instanceof NutnetSmsMessage)) {
            $message = new NutnetSmsMessage((string)$message);
        }

        $this->sender->send($phone, $message->getContent(), $message->getOptions());
    }
}