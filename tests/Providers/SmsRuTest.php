<?php
/**
 * 27/09/2019
 * @author Maksim Khodyrev <maximkou@gmail.com>
 */

namespace Tests\Providers;

use Nutnet\LaravelSms\Providers\SmsRu;
use Tests\BaseTestCase;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Auth\AuthInterface;
use Zelenin\SmsRu\Auth\LoginPasswordAuth;
use Zelenin\SmsRu\Auth\LoginPasswordSecureAuth;
use Zelenin\SmsRu\Entity\Sms;

class SmsRuTest extends BaseTestCase
{
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
}