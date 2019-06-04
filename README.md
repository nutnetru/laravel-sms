# Пакет Laravel-Sms

Этот пакет предоставляет класс для отправки смс и предустановленные реализации популярных провайдеров.

## Установка

Подключите пакет командой:
```bash
composer require nutnet/laravel-sms ~0.1
```

После того как пакет был установлен добавьте его сервис-провайдер в config/app.php:
```php
// config/app.php
'providers' => [
    ...
    Nutnet\LaravelSms\ServiceProvider::class,
];
```

Теперь необходимо перенести конфигурацию пакета в Laravel:
``` bash
php artisan vendor:publish
```

## Настройка
Все настройки находятся в файле `config/nutnet-laravel-sms.php`.
Настройки по умолчанию указаны ниже (используется отправка смс в лог-файл).

```php
/**
 * название класса-провайдера
 * @see Nutnet\LaravelSms\Providers
 */
'provider' => env('NUTNET_SMS_PROVIDER', \Nutnet\LaravelSms\Providers\Log::class),

/**
 * настройки, специфичные для провайдера
 */
'provider_options' => [
    'login' => env('NUTNET_SMS_LOGIN'),
    'password' => env('NUTNET_SMS_PASSWORD'),
],
```

**Пример файла .env:**
```bash
# полное название класса-провайдера
NUTNET_SMS_PROVIDER=\Nutnet\LaravelSms\Providers\Log

# реквизиты для доступа к API смс-провайдера
NUTNET_SMS_LOGIN=<ваш_логин>
NUTNET_SMS_PASSWORD=<ваш_пароль>
```

### Поддерживаемые провайдеры смс услуг

#### Log
Используется для локальной разработки. Смс-сообщения записываются в файл лога.

#### SMPP
Отправка соообщений через протокол SMPP. Требует для работы пакет `franzose/laravel-smpp`.

#### Sms.ru
Отправка сообщений через провайдера Sms.ru. Требует для работы пакет `zelenin/smsru`.
В настройках провайдера требуется указать логин и пароль:
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'login' => env('NUTNET_SMS_LOGIN'),
    'password' => env('NUTNET_SMS_PASSWORD'),
],
```

#### Smsc.ru
Отправка сообщений через провайдера Smsc.ru. Требует для работы установленный `curl`.
В настройках провайдера требуется указать логин и пароль:
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'login' => env('NUTNET_SMS_LOGIN'),
    'password' => env('NUTNET_SMS_PASSWORD'),
],
```

#### IqSms.ru (Смс-Дисконт)
Отправка сообщений через провайдера iqsms.ru. Требует для работы установленный `curl`.
В настройках провайдера требуется указать логин и пароль:
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'login' => env('NUTNET_SMS_LOGIN'),
    'password' => env('NUTNET_SMS_PASSWORD'),
],
```

## Отправка сообщений

Для отправки сообщений используется класс `Nutnet\LaravelSms\SmsSender`.
Пример отправки:

```php
class IndexController extends Controller
{
    public function sendSms(Nutnet\LaravelSms\SmsSender $smsSender)
    {
        // отправка сообщения на 1 номер
        $smsSender->send('89193216754', 'Здесь текст сообщений');
        
        // отправка сообщения на несколько номеров
        $smsSender->sendBatch(['89193216754', '89228764523'], 'Здесь текст сообщений');
                
        // ...
    }
}
```

## Использование в связке с Laravel Notifications

Пакет включает в себя канал для Laravel Notifications (`Nutnet\LaravelSms\Notification\NutnetSmsChannel`).

#### Настройка Notifiable-модели

Добавьте метод `routeNotificationForNutnetSms` в свою Notifiable-модель, например:

```php
public function routeNotificationForNutnetSms() {
    return $this->phone; // Метод должен возвращать номер телефона, на который будет отправлено уведомление.
}  
```

#### Пример Notification

```php
namespace App\Notifications;

use Nutnet\LaravelSms\Notification\NutnetSmsChannel;
use Nutnet\LaravelSms\Notification\NutnetSmsMessage;
use Illuminate\Notifications\Notification;

class ExampleNotification extends Notification
{
    public function via($notifiable)
    {
        return [NutnetSmsChannel::class];
    }
    
    public function toNutnetSms($notifiable)
    {
        return new NutnetSmsMessage('текст сообщения', ['параметр1' => 'значение1']);
        
        // или верните просто строку, равнозначно new NutnetSmsMessage('текст сообщения')
        // return 'текст сообщения';
    }
}
```