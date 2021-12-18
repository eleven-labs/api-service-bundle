<?php

declare(strict_types=1);

namespace ElevenLabs\ApiServiceBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ManifestCommand.
 */
class ManifestCommand extends Command
{
    protected static $defaultName = 'api:manifest';

    protected function configure()
    {
        $this
            ->setDescription('Removes unnecessary files from the recorder')
            ->addArgument('dir')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('dir');
        $file = \realpath(sprintf('%s/manifest.json', $dir));

        if (!\is_file($file)) {
            return ;
        }

        $files = array_keys(json_decode(file_get_contents($file) ?? '[]', true));
        foreach (scandir($dir) as $file) {
            if ('.' === $file[0]) {
                continue;
            }

            if (!\in_array($file, $files)) {
                unlink($file);
            }
        }
    }
}
