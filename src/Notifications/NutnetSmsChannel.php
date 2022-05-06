<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 04.06.19
 */

namespace Nutnet\LaravelSms\Notifications;

use Illuminate\Notifications\RoutesNotifications;
use Nutnet\LaravelSms\SmsSender;
use Illuminate\Notifications\Notification;

class NutnetSmsChannel
{
    public function __construct(private SmsSender $sender)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
		if (!method_exists($notifiable, 'routeNotificationFor')) {
			throw new \InvalidArgumentException(\sprintf(
				'$notifiable must use trait %s or implement "routeNotificationFor" method',
				RoutesNotifications::class,
			));
		}

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