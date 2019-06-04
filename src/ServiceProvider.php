<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 13.07.17
 */

namespace Nutnet\LaravelSms;

use Nutnet\LaravelSms\Providers;

/**
 * Class ServiceProvider
 * @package Nutnet\LaravelSms
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var array
     */
    private $providerAliases = [
        'log' => Providers\Log::class,
        'iqsms' => Providers\IqSmsRu::class,
        'smpp' => Providers\Smpp::class,
        'smscru' => Providers\SmscRu::class,
        'smsru' => Providers\SmsRu::class,
    ];

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config.php' => config_path('nutnet-laravel-sms.php')
        ]);
    }

    public function register()
    {
        $this->app->singleton(SmsSender::class, function ($app) {
            $providerClass = config('nutnet-laravel-sms.provider');
            if (array_key_exists($providerClass, $this->providerAliases)) {
                $providerClass = $this->providerAliases[$providerClass];
            }

            return new SmsSender(
                $app->make($providerClass, [
                    'options' => config('nutnet-laravel-sms.provider_options')
                ])
            );
        });
    }
}
