<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */
return [
    /**
     * название класса-провайдера
     * Доступные провайдеры:
     * * \Nutnet\LaravelSms\Providers\Log (alias: log)
     * * \Nutnet\LaravelSms\Providers\Smpp (alias: smpp)
     * * \Nutnet\LaravelSms\Providers\SmscRu (alias: smscru)
     * * \Nutnet\LaravelSms\Providers\SmsRu (alias: smsru)
     * * \Nutnet\LaravelSms\Providers\IqSmsRu (alias: iqsmsru)
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
];
