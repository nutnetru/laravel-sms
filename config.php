<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */
return [
    /**
     * название класса-провайдера
     * Доступные провайдеры:
     * * \Nutnet\LaravelSms\Providers\Log
     * * \Nutnet\LaravelSms\Providers\Smpp
     * * \Nutnet\LaravelSms\Providers\SmscRu
     * * \Nutnet\LaravelSms\Providers\SmsRu
     * * \Nutnet\LaravelSms\Providers\IqSmsRu
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
];
