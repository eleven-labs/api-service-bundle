<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Unit\DependencyInjection;

use ElevenLabs\ApiServiceBundle\DependencyInjection\ApiServiceExtension;
use Http\Adapter\Guzzle6\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveParameterPlaceHoldersPass;

class ApiServiceExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setParameter('kernel.debug', true);
        $this->setParameter('kernel.cache_dir', '/cache');

    }

    protected function getContainerExtensions()
    {
        return [
            new ApiServiceExtension(),
        ];
    }

    public function testConfigLoadDefault()
    {
        $this->load();

        foreach (['uri_factory', 'client', 'message_factory'] as $type) {
            $this->assertContainerBuilderHasAlias("api_service.$type", "httplug.$type");
        }
    }

    public function testHttpPlugIntegration()
    {
        // Register httplug service like the HttppludBundle SHOULD do
        $this->container->register('httplug.client', Client::class);
        $this->container->register('httplug.message_factory', GuzzleMessageFactory::class);
        $this->container->register('httplug.uri_factory', GuzzleUriFactory::class);

        $this->load();

        $this->assertContainerBuilderHasService('api_service.client', Client::class);
        $this->assertContainerBuilderHasService('api_service.message_factory', GuzzleMessageFactory::class);
        $this->assertContainerBuilderHasService('api_service.uri_factory', GuzzleUriFactory::class);
    }

    public function testConfigCacheConfig()
    {
        $this->load();
        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('api_service.cache_factory', 0, '/cache/api_service');
    }

    protected function compile()
    {
        $this->container->getCompilerPassConfig()->setOptimizationPasses([
            new ResolveParameterPlaceHoldersPass(),
            new ResolveDefinitionTemplatesPass(),
        ]);

        parent::compile();
    }

}
