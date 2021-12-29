<?php

declare(strict_types=1);

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
    private array $providers;
    private ?PaginationProviderInterface $matchedProvider;

    public function __construct(array $providers)
    {
        $this->providers = [];
        $this->matchedProvider = null;
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

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

    private function addProvider(PaginationProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
}
