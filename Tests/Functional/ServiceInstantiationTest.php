<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Functional;

use ElevenLabs\Api\Service\ApiService;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceInstantiationTest extends WebTestCase
{
    public function testApiServiceWithDefaultClient()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $apiService = $container->get('api_service.api.foo');

        self::assertInstanceOf(ApiService::class, $apiService);
    }

    public function testApiServiceWithASpecifiedClient()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $apiService = $container->get('api_service.api.bar');

        self::assertInstanceOf(ApiService::class, $apiService);
    }
}
