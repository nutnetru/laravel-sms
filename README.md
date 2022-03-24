# Пакет Laravel-Sms

Этот пакет предоставляет класс для отправки смс и предустановленные реализации популярных провайдеров.

## Установка

Подключите пакет командой:
```bash
composer require nutnet/laravel-sms
```

После того как пакет был установлен добавьте его сервис-провайдер в config/app.php (для версий Laravel ниже 5.5):
```php
// config/app.php
'providers' => [
    ...
    Nutnet\LaravelSms\ServiceProvider::class,
];
```

Теперь необходимо перенести конфигурацию пакета в Laravel:
``` bash
php artisan vendor:publish --provider="Nutnet\LaravelSms\ServiceProvider" --tag="config"
```

## Конфигурирование

**ВНИМАНИЕ:** в обновлении 0.8.0 изменился формат конфигурационного файла. Если вы обновились с более ранних версий, не забудьте актуализировать конфигурацию.

#### Log
Используется для локальной разработки. Смс-сообщения записываются в файл лога.
Не поддерживает передачу параметров сообщения.

Для включения данного провайдера добавьте в `.env` файл

```
NUTNET_SMS_PROVIDER=log
```

Для отправки сообщений в определенный канал/стек (например, в консоль браузера), используйте настройку `channels`: 

```php
// config/nutnet-laravel-sms.php
'providers' => [
    'log' => [
        /**
         * каналы, в которые публикуются сообщения
         * оставьте пустым, если хотите использовать общие настройки логирования
         * @see https://laravel.com/docs/5.8/logging#building-log-stacks
        */
        'channels' => ['slack', 'browser'], // для версий Laravel >=5.6
    ]
],
```

Пример настройки лог-канала для отправки сообщений в консоль браузера
```php
// config/logging.php
'browser' => [
    'driver' => 'monolog',
    'handler' => Monolog\Handler\BrowserConsoleHandler::class,
],
```

#### SMPP
Отправка соообщений через протокол SMPP. Требует для работы пакет `franzose/laravel-smpp`.
В данный момент не поддерживает передачу параметров сообщения.

Для включения данного провайдера добавьте в `.env` файл

```
NUTNET_SMS_PROVIDER=smpp
```

Все прочие настройки находятся в конфигурационном файле пакета `franzose/laravel-smpp`

#### Sms.ru
Отправка сообщений через провайдера Sms.ru. Требует для работы пакет `zelenin/smsru`.

Для включения данного провайдера добавьте в `.env` файл

```
NUTNET_SMS_PROVIDER=smsru
```

**Авторизация по паре логин-пароль:**
```php
// config/nutnet-laravel-sms.php
'providers' => [
    'smsru' => [
        'auth_type' => 'standard',
        'login' => '<your login>',
        'password' => '<your password>',
        'partner_id' => '<your partner_id>', // оставьте null, если не нужен
        'message_defaults' => []
    ]
],
```

**Усиленная авторизация по паре логин-пароль и api_id:**
```php
// config/nutnet-laravel-sms.php
'providers' => [
    'smsru' => [
        'auth_type' => 'secured',
        'login' => '<your login>',
        'password' => '<your password>',
        'api_id' => '<your api_id>',
        'partner_id' => '<your partner_id>', // оставьте null, если не нужен
        'message_defaults' => []
    ]
],
```

**Авторизация с использованием api_id:**
```php
// config/nutnet-laravel-sms.php
'providers' => [
    'smsru' => [
        'auth_type' => 'api_id',
        'api_id' => '<your api_id>',
        'partner_id' => '<your partner_id>', // оставьте null, если не нужен
        'message_defaults' => []
    ]
],
```

**Параметры сообщения:**
Поддерживается передача параметров сообщения (см. ниже в блоке "Отправка сообщений"). Полный список доступных параметров можно найти [здесь](https://sms.ru/api/send).

```php
$sender->send('<phone_number>', '<your_message>', [
    'translit' => 1,
    'test' => 1
]);
```

**Использовать собственный http-клиент вместо стандартного Zelenin\SmsRu\Client\Client:**

Просто зарегистрируйте свой http-клиент (например, `App\Services\SmsRuHttpClient`) в DI-контейнере следующим образом:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->bind(\Zelenin\SmsRu\Client\ClientInterface::class, \App\Services\SmsRuHttpClient::class);
}
```

#### Smsc.ru
Отправка сообщений через провайдера Smsc.ru. Требует для работы установленный `curl`.

Для включения данного провайдера добавьте в `.env` файл

```
NUTNET_SMS_PROVIDER=smscru
```

В настройках провайдера требуется указать логин и пароль:
```php
// config/nutnet-laravel-sms.php
'providers' => [
    'smscru' => [
        'login' => '<your_login>',
        'password' => '<your_password>',
        'message_defaults' => [],
    ]
],
```

Поддерживается передача параметров сообщения (см. ниже в блоке "Отправка сообщений").

#### IqSms.ru (Смс-Дисконт)
Отправка сообщений через провайдера iqsms.ru. Требует для работы установленный `curl`.

Для включения данного провайдера добавьте в `.env` файл

```
NUTNET_SMS_PROVIDER=iqsms
```

В настройках провайдера требуется указать логин и пароль:
```php
// config/nutnet-laravel-sms.php
'providers' => [
    'iqsms' => [
        'login' => '<your_login>',
        'password' => '<your_password>',
        'message_defaults' => [
            // to example, sender
            // 'sender' => 'Test',
        ]
    ]
],
```

Передача параметров сообщения поддерживается частично - разрешено передавать client_id (см. ниже в блоке "Отправка сообщений").

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
                
        // отправка сообщений с параметрами
        $sender->send('<phone_number>', '<your_message>', [
            'translit' => 1,
            'test' => 1
        ]);
        // ...
    }
}
```

**Задать параметры сообщения по умолчанию** можно в настройках провайдера, в опции `message_defaults`.

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