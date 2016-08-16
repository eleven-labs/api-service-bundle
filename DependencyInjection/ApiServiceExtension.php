<?php
namespace ElevenLabs\ApiServiceBundle\DependencyInjection;

use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\ApiServiceBundle\Factory\DummyService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
            $container->setAlias(sprintf('api_service.%s', $type), $config['default'][$type]);
        }

        $this->configureCacheConfig($container, $config['cache_dir']);
        $this->configureApiServices($container, $config['apis']);
    }

    public function configureCacheConfig(ContainerBuilder $container, $cacheDir)
    {
        $container->getDefinition('api_service.cache_factory')
            ->replaceArgument(0, $cacheDir)
            ->replaceArgument(1, $container->getParameter('kernel.debug'));
    }

    public function configureApiServices(ContainerBuilder $container, array $apiServices)
    {
        $serviceFactory = new Reference('api_service.factory.service');

        foreach ($apiServices as $name => $arguments) {
            $serviceId = 'api_service.api.'.$name;

            $httpClientServiceId = (isset($arguments['client']))
                ? $arguments['client']
                : 'api_service.client';

            $apiServiceFactory = $container
                ->register($serviceId, ApiService::class)
                ->setFactory([$serviceFactory, 'getService'])
                ->addArgument(new Reference($httpClientServiceId))
                ->addArgument($arguments['baseUrl'])
                ->addArgument($arguments['schema']);
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
