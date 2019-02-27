<?php

namespace ElevenLabs\ApiServiceBundle\Pagination;

use ElevenLabs\Api\Definition\ResponseDefinition;
use ElevenLabs\Api\Service\Pagination\PaginationProvider;
use Psr\Http\Message\ResponseInterface;

class PaginationProviderChain implements PaginationProvider
{
    /**
     * @var PaginationProvider[]
     */
    private $providers;

    /**
     * @var PaginationProvider
     */
    private $matchedProvider;

    /**
     * @param PaginationProvider[] $providers
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
    public function getPagination(array &$data, ResponseInterface $response, ResponseDefinition $responseDefinition)
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
    public function supportPagination(array $data, ResponseInterface $response, ResponseDefinition $responseDefinition)
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
     * @param PaginationProvider $provider
     */
    private function addProvider(PaginationProvider $provider)
    {
        $this->providers[] = $provider;
    }
}
