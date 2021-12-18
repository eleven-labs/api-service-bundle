<?php

declare(strict_types=1);

namespace ElevenLabs\ApiServiceBundle\Factory;

use ElevenLabs\Api\Decoder\DecoderInterface;
use ElevenLabs\Api\Factory\SchemaFactory;
use ElevenLabs\Api\Factory\SchemaFactoryInterface;
use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Validator\MessageValidator;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;
use JsonSchema\Validator;
use Rize\UriTemplate;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Create an API Service
 */
class ServiceFactory
{
    private UriFactory $uriFactory;
    private UriTemplate $uriTemplate;
    private MessageFactory $messageFactory;
    private Validator $validator;
    private SerializerInterface $serializer;
    private DecoderInterface $decoder;

    public function __construct(
        UriFactory $uriFactory,
        UriTemplate $uriTemplate,
        MessageFactory $messageFactory,
        Validator $validator,
        SerializerInterface $serializer,
        DecoderInterface $decoder
    ) {
        $this->uriFactory = $uriFactory;
        $this->uriTemplate = $uriTemplate;
        $this->messageFactory = $messageFactory;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->decoder = $decoder;
    }

    public function getService(HttpClient $httpClient, SchemaFactoryInterface $schemaFactory, string $schemaFile, array $config = []): ApiService
    {
        $schema = $schemaFactory->createSchema($schemaFile);

        return new ApiService(
            $this->uriFactory,
            $this->uriTemplate,
            $httpClient,
            $this->messageFactory,
            $schema,
            new MessageValidator($this->validator, $this->decoder),
            $this->serializer,
            $config
        );
    }
}
