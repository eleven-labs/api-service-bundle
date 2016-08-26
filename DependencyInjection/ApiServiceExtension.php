<?php
namespace ElevenLabs\ApiServiceBundle\DependencyInjection;

use ElevenLabs\Api\Decoder\Adapter\SymfonyDecoderAdapter;
use ElevenLabs\Api\Decoder\DecoderInterface;
use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Service\Denormalizer\ResourceDenormalizer;
use ElevenLabs\ApiServiceBundle\Factory\DummyService;
use JsonSchema\Validator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Serializer\Encoder\ChainDecoder;

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

        $this->configureSerializer($container);
        $this->configureApiServices($container, $config['apis'], $config['cache']);
    }

    public function configureSerializer(ContainerBuilder $container)
    {
        $container->setAlias('api_service.serializer', 'serializer');

        $definition = $container->register('api_service.normalizer.resource', ResourceDenormalizer::class);
        $definition->setPublic(false);
        $definition->addTag('serializer.normalizer', array('priority' => -890));
    }

    public function configureApiServices(ContainerBuilder $container, array $apiServices, array $cache)
    {
        $serviceFactoryRef = new Reference('api_service.factory');

        // Register decoder
        $definition = $container->register('api_service.decoder.symfony', ChainDecoder::class);
        $definition->setArguments([
            [
                new Reference('serializer.encoder.json'),
                new Reference('serializer.encoder.xml')
            ]
        ]);

        $definition = $container->register('api_service.decoder', SymfonyDecoderAdapter::class);
        $definition->setArguments([new Reference('api_service.decoder.symfony')]);

        // Register validator
        $validator = $container->register('api_service.json_schema_validator', Validator::class);
        $validator->setPublic(false);

        // Configure schema factory
        $schemaFactoryId = 'api_service.schema_factory.swagger';
        if ($cache['enabled']) {
            $schemaFactoryId = 'api_service.schema_factory.cached_factory';
            $schemaFactory = $container->getDefinition('api_service.schema_factory.cached_factory');
            $schemaFactory->replaceArgument(0, new Reference($cache['service']));
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
