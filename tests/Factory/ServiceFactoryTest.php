<?php

namespace ElevenLabs\ApiServiceBundle\Tests\Factory;

use ElevenLabs\Api\Decoder\DecoderInterface;
use ElevenLabs\Api\Factory\SchemaFactory;
use ElevenLabs\Api\Schema;
use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\ApiServiceBundle\Factory\ServiceFactory;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Rize\UriTemplate;
use Symfony\Component\Serializer\SerializerInterface;

class ServiceFactoryTest extends TestCase
{
    /** @test */
    public function itShouldReturnAService()
    {
        $aSchemaFile = 'schema.json';

        $uriFactory = $this->prophesize(UriFactory::class);
        $uriTemplate = $this->prophesize(UriTemplate::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $validator = $this->prophesize(Validator::class);
        $serializer = $this->prophesize(SerializerInterface::class);
        $decoder = $this->prophesize(DecoderInterface::class);

        $factory = new ServiceFactory(
            $uriFactory->reveal(),
            $uriTemplate->reveal(),
            $messageFactory->reveal(),
            $validator->reveal(),
            $serializer->reveal(),
            $decoder->reveal()
        );

        $httpClient = $this->prophesize(HttpClient::class);

        $schema = $this->prophesize(Schema::class);
        $schema->getSchemes()->willReturn(['http']);
        $schema->getHost()->willReturn('domain.tld');

        $schemaFactory = $this->prophesize(SchemaFactory::class);
        $schemaFactory->createSchema($aSchemaFile)->shouldBeCalledTimes(1)->willReturn($schema);

        $service = $factory->getService(
            $httpClient->reveal(),
            $schemaFactory->reveal(),
            $aSchemaFile,
            $config = []
        );

        self::assertInstanceOf(ApiService::class, $service);
    }
}
