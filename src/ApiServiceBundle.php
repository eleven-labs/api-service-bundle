<?php

declare(strict_types=1);

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
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new FormatPass())
            ->addCompilerPass(new PaginatorCompilerPass())
        ;
    }
}
