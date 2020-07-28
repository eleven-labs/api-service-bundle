<?php

namespace ElevenLabs\ApiServiceBundle\Pagination;

use ElevenLabs\Api\Definition\ResponseDefinition;
use ElevenLabs\Api\Service\Pagination\Pagination;
use ElevenLabs\Api\Service\Pagination\Provider\PaginationProviderInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PaginationProviderChain.
 */
class PaginationProviderChain implements PaginationProviderInterface
{
    /**
     * @var PaginationProviderInterface[]
     */
    private $providers;

    /**
     * @var PaginationProviderInterface
     */
    private $matchedProvider;

    /**
     * @param PaginationProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = [];
        $this->matchedProvider = null;
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * @param array              $data
     * @param ResponseInterface  $response
     * @param ResponseDefinition $responseDefinition
     *
     * @return mixed
     */
    public function getPagination(array &$data, ResponseInterface $response, ResponseDefinition $responseDefinition): Pagination
    {
        if ($this->matchedProvider === null) {
            throw new \LogicException('No pagination provider available');
        }

        $pagination = $this->matchedProvider->getPagination($data, $response, $responseDefinition);
        // reset matched provider
        $this->matchedProvider = null;

        return $pagination;
    }

    /**
     * @param array              $data
     * @param ResponseInterface  $response
     * @param ResponseDefinition $responseDefinition
     *
     * @return bool
     */
    public function supportPagination(array $data, ResponseInterface $response, ResponseDefinition $responseDefinition): bool
    {
        foreach ($this->providers as $index => $provider) {
            if ($provider->supportPagination($data, $response, $responseDefinition)) {
                $this->matchedProvider = $provider;

                return true;
            }
        }

        return false;
    }

    /**
     * @param PaginationProviderInterface $provider
     */
    private function addProvider(PaginationProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
