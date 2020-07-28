<?php

namespace ElevenLabs\ApiServiceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Class PaginatorCompilerPass.
 */
class PaginatorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        $providers = [];
        $configsProviders = $this->getPaginationConfigs($container);
        foreach ($container->findTaggedServiceIds('api_service.pagination_provider') as $id => $configs) {
            $provider = $container->getDefinition($id);
            $provider->replaceArgument(0, $configsProviders[$configs[0]['provider']] ?? []);
            $providers[] = $provider;
        }

        $pagination = $container->getDefinition('api_service.pagination_provider.chain');
        $pagination->replaceArgument(0, $providers);
    }

    private function getPaginationConfigs(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig('api_service');
        $config = reset($configs);

        return $config['pagination'] ?? [];
    }
}
