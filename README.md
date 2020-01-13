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

## Настройка
Все настройки находятся в файле `config/nutnet-laravel-sms.php`.
Настройки по умолчанию указаны ниже (используется отправка смс в лог-файл).

```php
/**
 * название класса-провайдера или его псевдоним (доступны log, iqsms, smpp, smscru, smsru)
 * @see Nutnet\LaravelSms\Providers
 */
'provider' => env('NUTNET_SMS_PROVIDER', 'log'),

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
# полное название класса-провайдера или псевдоним (log, iqsms, smpp, smscru, smsru)
NUTNET_SMS_PROVIDER=log

# реквизиты для доступа к API смс-провайдера
NUTNET_SMS_LOGIN=<ваш_логин>
NUTNET_SMS_PASSWORD=<ваш_пароль>
```

### Поддерживаемые провайдеры смс услуг

#### Log
Используется для локальной разработки. Смс-сообщения записываются в файл лога.
Не поддерживает передачу параметров сообщения.

#### SMPP
Отправка соообщений через протокол SMPP. Требует для работы пакет `franzose/laravel-smpp`.
В данный момент не поддерживает передачу параметров сообщения.

#### Sms.ru
Отправка сообщений через провайдера Sms.ru. Требует для работы пакет `zelenin/smsru`.

**Авторизация по паре логин-пароль:**
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'auth_type' => 'standard',
    'login' => '<your login>',
    'password' => '<your password>',
    'partner_id' => '<your partner_id>', // оставьте null, если не нужен
],
```

**Усиленная авторизация по паре логин-пароль и api_id:**
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'auth_type' => 'secured',
    'login' => '<your login>',
    'password' => '<your password>',
    'api_id' => '<your api_id>',
    'partner_id' => '<your partner_id>', // оставьте null, если не нужен
],
```

**Авторизация с использованием api_id:**
```php
// config/nutnet-laravel-sms.php
'provider_options' => [
    'auth_type' => 'api_id',
    'api_id' => '<your api_id>',
    'partner_id' => '<your partner_id>', // оставьте null, если не нужен
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

Поддерживается передача параметров сообщения (см. ниже в блоке "Отправка сообщений").

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