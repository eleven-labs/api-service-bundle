<?php
namespace ElevenLabs\ApiServiceBundle\Factory;

use ElevenLabs\Api\Decoder\DecoderInterface;
use ElevenLabs\Api\Factory\SchemaFactory;
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
    /** @var UriFactory */
    private $uriFactory;

    /** @var UriTemplate */
    private $uriTemplate;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var Validator */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    /** @var \ElevenLabs\Api\Decoder\DecoderInterface */
    private $decoder;

    /**
     * @param UriFactory $uriFactory
     * @param UriTemplate $uriTemplate
     * @param MessageFactory $messageFactory
     * @param Validator $validator
     * @param SerializerInterface $serializer
     * @param DecoderInterface $decoder
     */
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

    /**
     * @param HttpClient $httpClient
     * @param SchemaFactory $schemaFactory
     * @param $schemaFile
     * @param array $config
     *
     * @return ApiService
     */
    public function getService(
        HttpClient $httpClient,
        SchemaFactory $schemaFactory,
        $schemaFile,
        $config = []
    ) {
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
