<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Functional;

use ElevenLabs\Api\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceInstantiationTest extends WebTestCase
{
    public function testApiServiceWithDefaultClient()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $apiService = $container->get('api_service.api.with_default_client');

        self::assertInstanceOf(ApiService::class, $apiService);
    }

    public function testApiServiceWithASpecifiedClient()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $apiService = $container->get('api_service.api.with_client');

        self::assertInstanceOf(ApiService::class, $apiService);
    }
}
