<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Unit\Factory;

use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Service\UriTemplate\UriTemplate;
use ElevenLabs\ApiServiceBundle\Factory\ConfigCacheFactory;
use ElevenLabs\ApiServiceBundle\Factory\ServiceFactory;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Config\ConfigCacheInterface;

class ServiceFactoryTest extends TestCase
{
    public function setUp()
    {
        vfsStream::setup('cache', 0600, ['schema.json.cache' => '<?php return new stdClass;']);
    }

    /** @test */
    public function itShouldReturnAService()
    {
        $aBaseUrl = 'http://domain.tld';
        $aSchemaFile = 'schema.json';

        $uriFactory = $this->prophesize(UriFactory::class);
        $uriTemplate = $this->prophesize(UriTemplate::class);
        $httpClient = $this->prophesize(HttpClient::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $configCache = $this->prophesize(ConfigCacheInterface::class);
        $configCacheFactory = $this->prophesize(ConfigCacheFactory::class);

        $uriFactory->createUri($aBaseUrl)->willReturn($this->prophesize(UriInterface::class));

        $configCache->getPath()->willReturn(vfsStream::url('cache/schema.json.cache'));
        $configCache->isFresh()->willReturn(true);

        $configCacheFactory->getConfigCacheFrom($aSchemaFile)->willReturn($configCache);

        $factory = new ServiceFactory(
            $uriFactory->reveal(),
            $uriTemplate->reveal(),
            $httpClient->reveal(),
            $messageFactory->reveal(),
            $configCacheFactory->reveal()
        );

        $service = $factory->getService($aBaseUrl, $aSchemaFile);

        self::assertInstanceOf(ApiService::class, $service);
    }
}
