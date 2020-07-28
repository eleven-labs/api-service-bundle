<?php

namespace ElevenLabs\ApiServiceBundle\Tests\DependencyInjection;

use ElevenLabs\ApiServiceBundle\DependencyInjection\ApiServiceExtension;
use Http\Adapter\Guzzle6\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;
use JsonSchema\Validator;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Encoder\ChainDecoder;

class ApiServiceExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setParameter('kernel.debug', true);
    }

    protected function getContainerExtensions()
    {
        return [new ApiServiceExtension()];
    }

    public function testConfigLoadDefault()
    {
        $this->load();

        foreach (['uri_factory', 'client', 'message_factory'] as $type) {
            $this->assertContainerBuilderHasAlias("api_service.$type", "httplug.$type");
        }
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            null
        );
    }

    public function testItUseHttpPlugServicesByDefault()
    {
        // Given services registered by th HTTPlug bundle
        $this->container->register('httplug.client', Client::class);
        $this->container->register('httplug.message_factory', GuzzleMessageFactory::class);
        $this->container->register('httplug.uri_factory', GuzzleUriFactory::class);

        $this->load();

        $this->assertContainerBuilderHasService('api_service.client', Client::class);
        $this->assertContainerBuilderHasService('api_service.message_factory', GuzzleMessageFactory::class);
        $this->assertContainerBuilderHasService('api_service.uri_factory', GuzzleUriFactory::class);
        $this->assertContainerBuilderHasService('api_service.decoder.symfony', ChainDecoder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.decoder',
            0,
            new Reference('api_service.decoder.symfony')
        );
        $this->assertContainerBuilderHasService('api_service.json_schema_validator', Validator::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            null
        );
    }

    public function testItUseACachedSchemaFactory()
    {
        $this->load([
            'cache' => [
                'service' => 'fake.cache.service'
            ]
        ]);

        $this->assertContainerBuilderHasAlias(
            'api_service.schema_factory',
            'api_service.schema_factory.cached_factory'
        );

        $this->assertContainerBuilderHasAlias('api_service.serializer', 'serializer');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            null
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.schema_factory.cached_factory',
            0,
            new Reference('fake.cache.service')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.schema_factory.cached_factory',
            1,
            new Reference('api_service.schema_factory.swagger')
        );
    }

    public function testItProvideApiServices()
    {
        $this->load([
            'apis' => [
                'foo' => [
                    'schema' => '/path/to/schema.json'
                ]
            ]
        ]);

        $this->assertContainerBuilderHasService('api_service.api.foo');
        $this->assertContainerBuilderHasAlias('api_service.serializer', 'serializer');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            null
        );
    }

    public function testItProvidePagination()
    {
        $this->load([
            'pagination' => [
                'header' => []
            ]
        ]);

        $expectedReference = new Reference('api_service.pagination_provider.chain');
        $this->assertContainerBuilderHasAlias('api_service.serializer', 'serializer');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            $expectedReference
        );
    }
}
