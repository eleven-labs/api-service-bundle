<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Functional;

use ElevenLabs\Api\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceInstantiationTest extends WebTestCase
{
    public function testApiService()
    {
        static::bootKernel();
        $container = static::$kernel->getContainer();
        $apiService = $container->get('api_service.api.foo');

        self::assertInstanceOf(ApiService::class, $apiService);
    }
}
