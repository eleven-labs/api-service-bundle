<?php

namespace ElevenLabs\ApiServiceBundle\DependencyInjection;

use ElevenLabs\Api\Decoder\Adapter\SymfonyDecoderAdapter;
use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Service\Denormalizer\ResourceDenormalizer;
use ElevenLabs\Api\Service\Pagination\Provider\PaginationHeader;
use ElevenLabs\ApiServiceBundle\Pagination\PaginationProviderChain;
use JsonSchema\Validator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\Encoder\ChainDecoder;

/**
 * Class ApiServiceExtension.
 */
class ApiServiceExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        foreach (['client', 'message_factory', 'uri_factory'] as $type) {
            $container->setAlias(sprintf('api_service.%s', $type), $config['default_services'][$type]);
        }

        $this->configureSerializer($container, $config['pagination']);
        $this->configureApiServices($container, $config['apis'], $config['cache']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $paginationProviders
     */
    private function configureSerializer(ContainerBuilder $container, array $paginationProviders)
    {
        $container->setAlias('api_service.serializer', 'serializer');
        $denormalizer = $container->getDefinition('api_service.denormalizer.resource');

        if (!empty($paginationProviders)) {
            $denormalizer->replaceArgument(0, new Reference('api_service.pagination_provider.chain'));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $apiServices
     * @param array            $cache
     */
    private function configureApiServices(ContainerBuilder $container, array $apiServices, array $cache)
    {
        $serviceFactoryRef = new Reference('api_service.factory');

        // Register decoder
        $container->register('api_service.decoder.symfony', ChainDecoder::class);

        $definition = $container->register('api_service.decoder', SymfonyDecoderAdapter::class);
        $definition->setArguments([new Reference('api_service.decoder.symfony')]);

        // Register validator
        $validator = $container->register('api_service.json_schema_validator', Validator::class);
        $validator->setPublic(false);

        // Configure schema factory
        $schemaFactoryId = 'api_service.schema_factory.swagger';
        if ($cache['enabled']) {
            $schemaFactory = $container->getDefinition('api_service.schema_factory.cached_factory');
            $schemaFactory->replaceArgument(0, new Reference($cache['service']));
            $schemaFactory->replaceArgument(1, new Reference($schemaFactoryId));
            $schemaFactoryId = 'api_service.schema_factory.cached_factory';
        }

        $container->setAlias('api_service.schema_factory', $schemaFactoryId);

        // Configure each api services
        foreach ($apiServices as $name => $arguments) {
            $container
                ->register('api_service.api.'.$name, ApiService::class)
                ->setFactory([$serviceFactoryRef, 'getService'])
                ->addArgument(new Reference($arguments['client']))
                ->addArgument(new Reference($schemaFactoryId))
                ->addArgument($arguments['schema'])
                ->addArgument($arguments['config']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
