<?php
namespace ElevenLabs\ApiServiceBundle\Tests\Unit\Factory;

use ElevenLabs\ApiServiceBundle\Factory\ConfigCacheFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheInterface;

class ConfigCacheFactoryTest extends TestCase
{
    /** @test */
    public function itShouldReturnAConfigCache()
    {
        $configCache = (new ConfigCacheFactory('/cacheDir'))->getConfigCacheFrom('/someDir/file.ext');

        self::assertInstanceOf(ConfigCacheInterface::class, $configCache);
        self::assertSame('/cacheDir/file.ext.cache', $configCache->getPath());
    }

}
