<?php
/**
 * 05.03.2020
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests;

use Nutnet\LaravelSms\Contracts\Provider;
use Nutnet\LaravelSms\SmsSender;

class SmsSenderTest extends BaseTestCase
{
    public function testPassingSendDefaultOptions()
    {
        $provider = $this->createMock(Provider::class);
        $provider
            ->expects($this->once())
            ->method('send')
            ->with('999999', 'test msg', [
                'test_option' => 5,
                'test_option_2' => 7
            ]);
        $provider
            ->expects($this->once())
            ->method('sendBatch')
            ->with(['999999', '10101010'], 'test msg', [
                'test_option' => 5,
                'test_option_2' => 3
            ]);

        $sender = new SmsSender(
            $provider,
            [
                'test_option' => 5,
                'test_option_2' => 6
            ]
        );

        $sender->send('+999999', 'test msg', [
            'test_option_2' => 7
        ]);

        $sender->sendBatch(['999999', '+10101010'], 'test msg', [
            'test_option_2' => 3
        ]);
    }
}