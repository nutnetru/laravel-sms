<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests\Providers;

use Nutnet\LaravelSms\Providers\SmsRu;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;
use Zelenin\SmsRu\Api;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Auth\AuthInterface;
use Zelenin\SmsRu\Auth\LoginPasswordAuth;
use Zelenin\SmsRu\Auth\LoginPasswordSecureAuth;
use Zelenin\SmsRu\Entity\Sms;
use Zelenin\SmsRu\Entity\SmsPool;
use Zelenin\SmsRu\Response\SmsResponse;

class SmsRuTest extends BaseTestCase
{
    public function testSendOneMessage()
    {
        list($provider, $client) = $this->getSendingMocks();

        $messageTo = '79112238844';
        $message = 'Test message';

        $client->expects($this->exactly(2))
            ->method('smsSend')
            ->willReturn(
                new SmsResponse(120),
                new SmsResponse(SmsRu::CODE_OK)
            )
            ->with($this->callback(function ($sms) use ($messageTo, $message) {
                if (!($sms instanceof Sms)) {
                    return false;
                }

                return $sms->to == $messageTo && $sms->text == $message;
            }));

        $this->assertFalse($provider->send($messageTo, $message));
        $this->assertTrue($provider->send($messageTo, $message));
    }

    public function testSendBatch()
    {
        list($provider, $client) = $this->getSendingMocks();

        $messageTo = ['79112238844', '79991112233', '79129998877'];
        $message = 'Test message';

        $client->expects($this->exactly(2))
            ->method('smsSend')
            ->willReturn(
                new SmsResponse(SmsRu::CODE_OK),
                new SmsResponse(300)
            )
            ->with($this->callback(function ($smsPool) use ($messageTo, $message) {
                if (!($smsPool instanceof SmsPool)) {
                    return false;
                }

                $recipients = array_map(function (Sms $sms) {
                    return $sms->to;
                }, $smsPool->messages);
                $messages = array_unique(array_map(function (Sms $sms) {
                    return $sms->text;
                }, $smsPool->messages));

                if (count($messages) > 1 || reset($messages) != $message) {
                    return false;
                }

                return count(array_intersect($recipients, $messageTo)) == count($messageTo);
            }));

        $this->assertTrue($provider->sendBatch($messageTo, $message));
        $this->assertFalse($provider->sendBatch($messageTo, $message));
    }

    public function testGettingClient()
    {
        $provider = new SmsRu([
            'login' => 'test',
            'password' => 'test',
        ]);

        $this->assertInstanceOf(Api::class, $provider->getApi());
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreatingClientWithStandardAuth()
    {
        $partnerId = 2;
        $login = 'test';
        $password = 'test_password';

        $auth = $this->callAuthCreator(new SmsRu([
            'login' => $login,
            'password' => $password,
            'partner_id' => $partnerId
        ]));

        $this->assertInstanceOf(LoginPasswordAuth::class, $auth);
        $this->assertEquals($partnerId, $auth->getPartnerId());
        $this->assertEquals($login, $auth->getAuthParams()['login']);
        $this->assertEquals($password, $auth->getAuthParams()['password']);
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreatingClientWithSecureAuth()
    {
        $apiId = 5;
        $login = 'test';
        $password = 'test_password';

        $auth = $this->callAuthCreator(new SmsRu([
            'auth_type' => SmsRu::AUTH_SECURED,
            'login' => $login,
            'password' => $password,
            'api_id' => $apiId,
        ]));

        $this->assertInstanceOf(LoginPasswordSecureAuth::class, $auth);
        $this->assertNull($auth->getPartnerId());
        $this->assertEquals($apiId, $auth->getApiId());
    }

    /**
     * @throws \ReflectionException
     */
    public function testCreatingClientWithApiIdAuth()
    {
        $apiId = 5;
        $partnerId = 10;

        $auth = $this->callAuthCreator(new SmsRu([
            'auth_type' => SmsRu::AUTH_API_ID,
            'api_id' => $apiId,
            'partner_id' => $partnerId
        ]));

        $this->assertInstanceOf(ApiIdAuth::class, $auth);
        $this->assertEquals($partnerId, $auth->getPartnerId());
        $this->assertEquals($apiId, $auth->getApiId());
    }

    /**
     * @throws \ReflectionException
     */
    public function testMakingMessage()
    {
        $provider = new SmsRu([]);
        $method = $this->makeMethodAccessible(SmsRu::class, 'makeMessage');

        /** @var Sms $result */
        $result = $method->invoke($provider, '098765', 'test_message');
        $this->assertNull($result->test);

        $result = $method->invoke($provider, '098765', 'test_message', [
            'test' => 1,
            'translit' => 1
        ]);

        $this->assertEquals($result->test, 1);
        $this->assertEquals($result->translit, 1);
    }

    /**
     * @param $provider
     * @return AuthInterface
     * @throws \ReflectionException
     */
    private function callAuthCreator($provider)
    {
        return $this->makeMethodAccessible(SmsRu::class, 'getAuth')->invoke($provider);
    }

    /**
     * @return array
     */
    private function getSendingMocks()
    {
        $client = $this->getMockBuilder(Api::class)
            ->disableOriginalConstructor()
            ->setMethods(['smsSend'])
            ->getMock();

        /** @var SmsRu|MockObject $provider */
        $provider = $this->getMockBuilder(SmsRu::class)
            ->setConstructorArgs([
                [
                    'login' => 'test',
                    'password' => 'test',
                ]
            ])
            ->setMethods(['getApi'])
            ->getMock();

        $provider->method('getApi')->willReturn($client);

        return [$provider, $client];
    }
}