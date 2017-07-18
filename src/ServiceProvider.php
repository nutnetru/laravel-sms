<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 13.07.17
 */

namespace Nutnet\LaravelSms;

/**
 * Class ServiceProvider
 * @package Nutnet\LaravelSms
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config.php' => config_path('nutnet-laravel-sms.php')
        ]);
    }

    public function register()
    {
        $this->app->singleton(SmsSender::class, function ($app) {
            return new SmsSender(
                $app->make(config('nutnet-laravel-sms.provider'), [
                    'options' => config('nutnet-laravel-sms.provider_options')
                ])
            );
        });
    }
}
