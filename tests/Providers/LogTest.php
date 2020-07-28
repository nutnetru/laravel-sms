<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests\Providers;

use Illuminate\Log\Logger;
use Illuminate\Log\LogManager;
use Nutnet\LaravelSms\Providers\Log;
use Psr\Log\NullLogger;
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

        $this->assertTrue($provider->send($to, $msg));
        $this->assertTrue($store->hasDebugThatContains($this->formatMsg($to, $msg)));
    }

    public function testSendBatch()
    {
        $store = new TestLogger();
        $provider = new Log($store);

        $to = ['79112238844', '79991112233', '79129998877'];
        $msg = 'Test';

        $this->assertTrue($provider->sendBatch($to, $msg));
        foreach ($to as $phone) {
            $this->assertTrue($store->hasDebugThatContains($this->formatMsg($phone, $msg)));
        }
    }

    // check sending, when set single channel
    public function testIsUsedLogChannel()
    {
        $channel = 'browser';
        $writer = new TestLogger();

        $store = $this
            ->getMockBuilder(LogManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['channel'])
            ->getMock();

        $store->expects($this->once())
            ->method('channel')
            ->with($channel)
            ->willReturn($writer);

        $provider = new Log($store, [
            'channels' => [$channel]
        ]);

        $this->assertEquals($writer, $provider->getWriter());
    }

    // check sending, when set multiple channels
    public function testIsUsedLogStack()
    {
        $channels = ['browser', 'syslog'];
        $writer = new Logger(new TestLogger());

        $store = $this
            ->getMockBuilder(LogManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['stack'])
            ->getMock();

        $store->expects($this->once())
            ->method('stack')
            ->with($channels)
            ->willReturn($writer);

        $provider = new Log($store, [
            'channels' => $channels
        ]);

        $this->assertEquals($writer, $provider->getWriter());
    }

    public function testUsingNonStackableLoggerWithStack()
    {
        $channels = ['browser', 'syslog'];

        $store = $this
            ->getMockBuilder(NullLogger::class)
            ->disableOriginalConstructor()
            ->setMethods(['channel'])
            ->getMock();

        $this->expectException(\DomainException::class);

        new Log($store, [
            'channels' => $channels
        ]);
    }

    // check sending, when channels is not set
    public function testIsUsedDefaultLogger()
    {
        $channels = [];
        $defaultLogDriver = new TestLogger();

        $store = $this
            ->getMockBuilder(LogManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['stack', 'channel', 'driver'])
            ->getMock();

        $store->expects($this->never())->method('stack');
        $store->expects($this->never())->method('channel');
        $store->method('driver')->willReturn($defaultLogDriver);

        $provider = new Log($store, [
            'channels' => $channels
        ]);

        $this->assertEquals($defaultLogDriver, $provider->getWriter()->driver());
    }

    private function formatMsg($to, $msg)
    {
        return sprintf('Sms is sent to %s: "%s"', $to, $msg);
    }
}