<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Service\Api\Gitlab\Branches;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

#[CoversClass(Branches::class)]
class BranchesTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $client;
    private Branches                       $branches;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client   = $this->createMock(HttpClientInterface::class);
        $this->branches = new Branches($this->logger, $this->client);
    }

    /**
     * @throws Throwable
     */
    public function testGetBranchFailure(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(400);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/123/repository/branches/remote-ref')
            ->willReturn($response);

        static::assertNull($this->branches->getBranch(123, 'remote-ref'));
    }

    /**
     * @throws Throwable
     */
    public function testGetBranchSuccess(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(200);
        $response->expects($this->once())->method('toArray')->willReturn(['foo' => 'bar']);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/123/repository/branches/remote-ref')
            ->willReturn($response);

        static::assertSame(['foo' => 'bar'], $this->branches->getBranch(123, 'remote-ref'));
    }
}
