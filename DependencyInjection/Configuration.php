<?php

namespace ElevenLabs\ApiServiceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author David Buchmann <mail@davidbu.ch>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Whether to use the debug mode.
     *
     * @see https://github.com/doctrine/DoctrineBundle/blob/v1.5.2/DependencyInjection/Configuration.php#L31-L41
     *
     * @var bool
     */
    private $debug;

    /**
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('api_service');

        $rootNode
            ->children()
                ->scalarNode('cache_dir')->info('Cache directory for schema')->defaultValue('%kernel.cache_dir%/api_service')->end()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->info('Configure which services to use when generation API service classes')
                    ->children()
                        ->scalarNode('client')->defaultValue('httplug.client')->end()
                        ->scalarNode('message_factory')->defaultValue('httplug.message_factory')->end()
                        ->scalarNode('uri_factory')->defaultValue('httplug.uri_factory')->end()
                    ->end()
                ->end()
                ->arrayNode('apis')
                    ->info('Declare API services')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('baseUrl')->info('The url of your service (ex: http://domain.tld)')->isRequired()->end()
                        ->scalarNode('schema')->info('absolute path to the OpenAPI/Swagger2.0 schema')->isRequired()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
