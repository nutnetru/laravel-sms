<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests\Providers;

use Nutnet\LaravelSms\Providers\Log;
use Nutnet\LaravelSms\Providers\SmscRu;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\Test\TestLogger;
use Tests\BaseTestCase;

class SmscRuTest extends BaseTestCase
{
    public function testSendOneMessage()
    {
        /** @var MockObject|SmscRu $provider */
        $provider = $this->getMockBuilder(SmscRu::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendBatch'])
            ->getMock();

        $to = '79991112233';
        $msg = 'Test';
        $options = ['test' => 1];

        $provider->expects($this->once())
            ->method('sendBatch')
            ->with(
                $this->equalTo([$to]),
                $this->equalTo($msg),
                $this->equalTo($options)
            );

        $provider->send($to, $msg, $options);
    }

    /**
     * @param $sendResponse
     * @param $expectedReturnValue
     * @dataProvider dpSendBatch
     */
    public function testSendBatch($sendResponse, $expectedReturnValue)
    {
        $login = $password = 'test';

        /** @var MockObject|SmscRu $provider */
        $provider = $this->getMockBuilder(SmscRu::class)
            ->setConstructorArgs([
                compact('login', 'password')
            ])
            ->setMethods(['doRequest'])
            ->getMock();

        $to = ['79112238844', '79991112233', '79129998877'];
        $msg = 'Test';
        $options = ['test_1' => 1];

        $provider->expects($this->once())
            ->method('doRequest')
            ->with($this->equalTo(array_merge(
                [
                    'login' => $login,
                    'psw' => md5($password),
                    'phones' => implode(SmscRu::PHONE_DELIMITER, $to),
                    'mes' => mb_convert_encoding($msg, 'Windows-1251'),
                    'fmt' => 3
                ],
                $options
            )))
            ->willReturn($sendResponse);

        $result = $provider->sendBatch($to, $msg, $options);

        $this->assertIsBool($result);
        $this->assertEquals($expectedReturnValue, $result);
    }

    /**
     * @return array
     */
    public function dpSendBatch()
    {
        return [
            [
                ['success' => true],
                true,
            ],
            [
                ['error' => 'Bad call'],
                false,
            ],
            [
                false,
                false,
            ],
            [
                0,
                false,
            ]
        ];
    }
}