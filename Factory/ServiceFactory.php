<?php
namespace ElevenLabs\ApiServiceBundle\Factory;

use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Service\UriTemplate\UriTemplate;
use ElevenLabs\Api\Validator\RequestValidator;
use ElevenLabs\Api\Validator\SchemaLoader;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use JsonSchema\Validator;

/**
 * Create an API Service
 */
class ServiceFactory
{
    /** @var UriFactory */
    private $uriFactory;

    /** @var UriTemplate */
    private $uriTemplate;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var ConfigCacheFactory */
    private $cacheFactory;

    /**
     * ServiceFactory constructor.
     * @param UriFactory $uriFactory
     * @param UriTemplate $uriTemplate
     * @param MessageFactory $messageFactory
     * @param ConfigCacheFactory $cacheFactory
     */
    public function __construct(
        UriFactory $uriFactory,
        UriTemplate $uriTemplate,
        MessageFactory $messageFactory,
        ConfigCacheFactory $cacheFactory
    ) {
        $this->uriFactory = $uriFactory;
        $this->uriTemplate = $uriTemplate;
        $this->messageFactory = $messageFactory;
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * @param HttpClient $httpClient
     * @param string $baseUrl
     * @param string $schemaFile
     *
     * @return ApiService
     */
    public function getService(HttpClient $httpClient, $baseUrl, $schemaFile)
    {
        $cache = $this->cacheFactory->getConfigCacheFrom($schemaFile);
        $schema = (new SchemaLoader($cache))->load($schemaFile);

        return new ApiService(
            $this->uriFactory->createUri($baseUrl),
            $this->uriTemplate,
            $httpClient,
            $this->messageFactory,
            $schema,
            new RequestValidator($schema, new Validator())
        );
    }
}
