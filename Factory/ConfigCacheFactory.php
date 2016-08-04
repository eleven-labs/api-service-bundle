<?php
namespace ElevenLabs\ApiServiceBundle\Factory;

use Symfony\Component\Config\ConfigCache;

class ConfigCacheFactory
{
    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $debug;

    public function __construct($cacheDir, $debug = false)
    {
        $this->cacheDir = $cacheDir;
        $this->debug = (bool) $debug;
    }

    public function getConfigCacheFrom($filename)
    {
        $cacheFile = $this->cacheDir.'/'.pathinfo($filename, PATHINFO_BASENAME).'.cache';

        return new ConfigCache($cacheFile, $this->debug);
    }
}
