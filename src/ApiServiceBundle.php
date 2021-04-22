<?php

namespace ElevenLabs\ApiServiceBundle;

use ElevenLabs\ApiServiceBundle\DependencyInjection\Compiler\FormatPass;
use ElevenLabs\ApiServiceBundle\DependencyInjection\Compiler\PaginatorCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ApiServiceBundle.
 */
class ApiServiceBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new FormatPass())
            ->addCompilerPass(new PaginatorCompilerPass())
        ;
    }
}
