<?php

namespace ElevenLabs\ApiServiceBundle\Tests;

use ElevenLabs\ApiServiceBundle\ApiServiceBundle;
use ElevenLabs\ApiServiceBundle\DependencyInjection\Compiler\FormatPass;
use ElevenLabs\ApiServiceBundle\DependencyInjection\Compiler\PaginatorCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ApiServiceBundle.
 */
class ApiServiceBundleTest extends TestCase
{
    public function testShouldAddCompilerPass()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->exactly(2))
            ->method('addCompilerPass')
            ->willReturnCallback(function ($pass) use ($container) {
                $this->assertTrue($pass instanceof FormatPass || $pass instanceof PaginatorCompilerPass);

                return $container;
            })
        ;

        $bundle = new ApiServiceBundle();
        $bundle->build($container);
    }
}
