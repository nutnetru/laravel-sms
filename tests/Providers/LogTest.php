<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests\Providers;

use Nutnet\LaravelSms\Providers\Log;
use Psr\Log\Test\TestLogger;
use Tests\BaseTestCase;

class LogTest extends BaseTestCase
{
    public function testSendOneMessage()
    {
        $store = new TestLogger();
        $provider = new Log($store);

        $to = '79991112233';
        $msg = 'Test';

        $provider->send($to, $msg);
        $this->assertTrue($store->hasDebugThatContains($this->formatMsg($to, $msg)));
    }

    public function testSendBatch()
    {
        $store = new TestLogger();
        $provider = new Log($store);

        $to = ['79112238844', '79991112233', '79129998877'];
        $msg = 'Test';

        $provider->sendBatch($to, $msg);
        foreach ($to as $phone) {
            $this->assertTrue($store->hasDebugThatContains($this->formatMsg($phone, $msg)));
        }
    }

    private function formatMsg($to, $msg)
    {
        return sprintf('Sms is sent to %s: "%s"', $to, $msg);
    }
}