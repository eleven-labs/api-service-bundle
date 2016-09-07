<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Unit\DependencyInjection;

use ElevenLabs\ApiServiceBundle\DependencyInjection\Configuration;
use ElevenLabs\ApiServiceBundle\DependencyInjection\ApiServiceExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{

    protected function getContainerExtension()
    {
        return new ApiServiceExtension();
    }

    protected function getConfiguration()
    {
        return new Configuration(true);
    }

    public function testEmptyConfiguration()
    {
        $expectedEmptyConfig = [
            'default_services' => [
                'client' => 'httplug.client',
                'message_factory' => 'httplug.message_factory',
                'uri_factory' => 'httplug.uri_factory',
            ],
            'cache' => [
                'enabled' => false
            ],
            'pagination' => [],
            'apis' => [],
        ];

        $fixturesPath =  __DIR__.'/../../Resources/Fixtures';

        $this->assertProcessedConfigurationEquals(
            $expectedEmptyConfig,
            [$fixturesPath.'/config/empty.yml']
        );
    }

    public function testSupportsAllConfigFormats()
    {
        $expectedConfiguration = [
            'default_services' => [
                'client' => 'httplug.client.acme',
                'uri_factory' => 'my.uri_factory',
                'message_factory' => 'my.message_factory',
            ],
            'cache' => [
                'enabled' => true,
                'service' => 'my.psr6_cache_impl'
            ],
            'pagination' => [
                'header' => [
                    'page' => 'X-Page',
                    'perPage' => 'X-Per-Page',
                    'totalPages' => 'X-Total-Pages',
                    'totalItems' => 'X-Total-Items',
                ]
            ],
            'apis' => [
                'foo' => [
                    'schema' => '/path/to/foo.yml',
                    'client' => 'api_service.client',
                    'config' => [
                        'baseUri' => null,
                        'validateRequest' => true,
                        'validateResponse' => false,
                        'returnResponse' => false
                    ]
                ],
                'bar' => [
                    'schema' => '/path/to/bar.json',
                    'client' => 'httplug.client.bar',
                    'config' => [
                        'baseUri' => 'https://bar.com',
                        'validateRequest' => false,
                        'validateResponse' => true,
                        'returnResponse' => true
                    ]
                ]
            ],
        ];

        $fixturesPath =  __DIR__.'/../../Resources/Fixtures';

        $this->assertProcessedConfigurationEquals(
            $expectedConfiguration,
            [$fixturesPath.'/config/full.yml']
        );
    }
}
