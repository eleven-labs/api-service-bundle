<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Unit\DependencyInjection;

use ElevenLabs\ApiServiceBundle\DependencyInjection\Configuration;
use ElevenLabs\ApiServiceBundle\DependencyInjection\ApiServiceExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;

/**
 * @author David Buchmann <mail@davidbu.ch>
 */
class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    private $emptyConfig = [
        'cache_dir' => '%kernel.cache_dir%/api_service',
        'default' => [
            'client' => 'httplug.client',
            'message_factory' => 'httplug.message_factory',
            'uri_factory' => 'httplug.uri_factory',
        ],
        'apis' => [],
    ];

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
        $fixturesPath =  __DIR__.'/../../Resources/Fixtures';

        $this->assertProcessedConfigurationEquals($this->emptyConfig, [$fixturesPath.'/config/empty.yml']);
    }

    public function testSupportsAllConfigFormats()
    {
        $fixturesPath =  __DIR__.'/../../Resources/Fixtures';

        $expectedConfiguration = [
            'cache_dir' => '/path/to/cache/dir',
            'default' => [
                'client' => 'custom_client',
                'uri_factory' => 'custom_uri_factory',
                'message_factory' => 'custom_message_factory',
            ],
            'apis' => [
                'foo' => [
                    'baseUrl' => 'https://foo.com',
                    'schema' => '/path/to/foo.yml'
                ],
                'bar' => [
                    'baseUrl' => 'https://bar.com',
                    'schema' => '/path/to/bar.json'
                ]
            ],
        ];

        $this->assertProcessedConfigurationEquals($expectedConfiguration, [$fixturesPath.'/config/full.yml']);
    }
}
