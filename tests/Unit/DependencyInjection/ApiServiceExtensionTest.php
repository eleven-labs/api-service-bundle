<?php
namespace ElevenLabs\ApiServiceBundle\DependencyInjection;

use Http\Adapter\Guzzle6\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

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
    }

    public function testItProvidePagination()
    {
        $this->load([
            'pagination' => [
                'header' => []
            ]
        ]);

        $expectedReference = new Reference('api_service.pagination_provider.chain');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.denormalizer.resource',
            0,
            $expectedReference
        );

        $expectedReferences = [new Reference('api_service.pagination_provider.header')];
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'api_service.pagination_provider.chain',
            0,
            $expectedReferences
        );
    }
}
