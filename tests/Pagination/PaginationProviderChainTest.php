<?php

namespace ElevenLabs\ApiServiceBundle\Tests\Pagination;

use ElevenLabs\Api\Definition\ResponseDefinition;
use ElevenLabs\Api\Service\Pagination\Pagination;
use ElevenLabs\Api\Service\Pagination\Provider\PaginationProviderInterface;
use ElevenLabs\ApiServiceBundle\Pagination\PaginationProviderChain;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

class PaginationProviderChainTest extends TestCase
{
    /** @test */
    public function itMustNotSupportPaginationBecauseThereAreNotProvider()
    {
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ResponseDefinition|MockObject $responseDefinition */
        $responseDefinition = $this->getMockBuilder(ResponseDefinition::class)->disableOriginalConstructor()->getMock();

        $provider = new PaginationProviderChain([]);
        $this->assertFalse($provider->supportPagination([], $response, $responseDefinition));
    }

    /** @test */
    public function itMustNotSupportPagination()
    {
        $providers = [];
        for ($i = 0; $i < 1; $i++) {
            $provider = $this->createMock(PaginationProviderInterface::class);
            $provider->expects($this->once())->method('supportPagination')->willReturn(false);
            $providers[] = $provider;
        }

        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ResponseDefinition|MockObject $responseDefinition */
        $responseDefinition = $this->getMockBuilder(ResponseDefinition::class)->disableOriginalConstructor()->getMock();

        $provider = new PaginationProviderChain($providers);
        $this->assertFalse($provider->supportPagination([], $response, $responseDefinition));
    }

    /** @test */
    public function itMustSupportPagination()
    {
        $providers = [];
        for ($i = 0; $i < 1; $i++) {
            $provider = $this->createMock(PaginationProviderInterface::class);
            $provider->expects($this->once())->method('supportPagination')->willReturn(true);
            $providers[] = $provider;
        }

        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ResponseDefinition|MockObject $responseDefinition */
        $responseDefinition = $this->getMockBuilder(ResponseDefinition::class)->disableOriginalConstructor()->getMock();

        $provider = new PaginationProviderChain($providers);
        $this->assertTrue($provider->supportPagination([], $response, $responseDefinition));
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     *
     * @expectedExceptionMessage No pagination provider available
     */
    public function itMustNotReturnPaginationBecauseThrowException()
    {
        $data = [];
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ResponseDefinition|MockObject $responseDefinition */
        $responseDefinition = $this->getMockBuilder(ResponseDefinition::class)->disableOriginalConstructor()->getMock();

        $provider = new PaginationProviderChain([]);
        $provider->getPagination($data, $response, $responseDefinition);
    }

    /** @test */
    public function itMustReturnPagination()
    {
        $data = [];
        $pagination = $this->getMockBuilder(Pagination::class)->disableOriginalConstructor()->getMock();

        $provider = $this->createMock(PaginationProviderInterface::class);
        $provider->expects($this->once())->method('supportPagination')->willReturn(true);
        $provider->expects($this->once())->method('getPagination')->willReturn($pagination);


        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ResponseDefinition|MockObject $responseDefinition */
        $responseDefinition = $this->getMockBuilder(ResponseDefinition::class)->disableOriginalConstructor()->getMock();

        $provider = new PaginationProviderChain([$provider]);
        $this->assertTrue($provider->supportPagination([], $response, $responseDefinition));
        $provider->getPagination($data, $response, $responseDefinition);
    }
}
