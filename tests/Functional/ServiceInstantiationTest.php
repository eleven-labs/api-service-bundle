<?php

namespace ElevenLabs\ApiServiceBundle;

use ElevenLabs\Api\Service\ApiService;
use ElevenLabs\Api\Service\Resource\Collection;
use Http\Message\MessageFactory;
use Http\Mock\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceInstantiationTest extends WebTestCase
{
    private $container;

    public function setUp()
    {
        static::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    public function testApiServiceWithDefaultClient()
    {
        $apiService = $this->container->get('api_service.api.foo');

        assertThat($apiService, isInstanceOf(ApiService::class));
    }

    public function testApiServiceWithASpecifiedClient()
    {
        $apiService = $this->container->get('api_service.api.bar');

        assertThat($apiService, isInstanceOf(ApiService::class));
    }

    public function testAnApiServiceThatReturnResponseObjects()
    {
        /** @var Client $clientMock */
        $clientMock = $this->container->get('httplug.client.mock');
        /** @var MessageFactory $messageFactory */
        $messageFactory = $this->container->get('httplug.message_factory');
        /** @var ApiService $apiService */
        $apiService = $this->container->get('api_service.api.bar');

        $clientMock->addResponse(
            $messageFactory->createResponse(201)
        );

        $response = $apiService->call('addFoo');
        assertThat($response->getStatusCode(), equalTo(201));

        $clientMock->addResponse(
            $messageFactory->createResponse(
                200,
                '',
                [
                    'Content-Type'  => 'application/json'
                ],
                json_encode(
                    [
                        [
                            'id'   => 1,
                            'name' => 'foo'
                        ],
                        [
                            'id'   => 2,
                            'name' => 'foo'
                        ]
                    ]
                )
            )
        );

        $response = $apiService->call('getFooCollection');
        assertThat($response->getStatusCode(), equalTo(200));
    }

    public function testAnApiServiceThatReturnAResource()
    {
        /** @var Client $clientMock */
        $clientMock = $this->container->get('httplug.client.mock');
        /** @var MessageFactory $messageFactory */
        $messageFactory = $this->container->get('httplug.message_factory');
        /** @var ApiService $apiService */
        $apiService = $this->container->get('api_service.api.foo');

        $clientMock->addResponse(
            $messageFactory->createResponse(
                200,
                '',
                [
                    'Content-Type'  => 'application/json',
                    'X-Page'        => '1',
                    'X-Per-Page'    => '2',
                    'X-Total-Pages' => '4',
                    'X-Total-Items' => '8',
                ],
                json_encode(
                    [
                        [
                            'id'   => 1,
                            'name' => 'foo'
                        ],
                        [
                            'id'   => 2,
                            'name' => 'foo'
                        ]
                    ]
                )
            )
        );

        /** @var Collection $resource */
        $resource = $apiService->call('getFooCollection');

        assertThat($resource, isInstanceOf(Collection::class));
        assertThat($resource->getData(), countOf(2));

        $pagination = $resource->getPagination();
        assertThat($pagination->getPage(), equalTo('1'));
        assertThat($pagination->getPerPage(), equalTo('2'));
        assertThat($pagination->getTotalPages(), equalTo('4'));
        assertThat($pagination->getTotalItems(), equalTo('8'));
    }
}
