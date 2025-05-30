<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Version;
use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

#[CoversClass(MergeRequests::class)]
class MergeRequestsTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $client;
    private SerializerInterface&MockObject $serializer;
    private MergeRequests                  $mergeRequests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client        = $this->createMock(HttpClientInterface::class);
        $this->serializer    = $this->createMock(SerializerInterface::class);
        $this->mergeRequests = new MergeRequests($this->client, $this->serializer);
    }

    /**
     * @throws Throwable
     */
    public function testFindByRemoteRef(): void
    {
        $data = ['id' => 1, 'iid' => 2, 'title' => 'foo', 'web_url' => 'bar'];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([$data]);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/111/merge_requests', ['query' => ['scope' => 'all', 'per_page' => 1, 'source_branch' => 'foo']])
            ->willReturn($response);

        static::assertSame($data, $this->mergeRequests->findByRemoteRef(111, 'foo'));
    }

    /**
     * @throws Throwable
     */
    public function testFindByRemoteRefNotFound(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([]);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/111/merge_requests', ['query' => ['scope' => 'all', 'per_page' => 1, 'source_branch' => 'foo']])
            ->willReturn($response);

        static::assertNull($this->mergeRequests->findByRemoteRef(111, 'foo'));
    }

    /**
     * @throws Throwable
     */
    public function testVersions(): void
    {
        $version = new Version();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('json');

        $this->client->expects($this->once())->method('request')
            ->with('GET', 'projects/111/merge_requests/222/versions')
            ->willReturn($response);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('json', Version::class . '[]', JsonEncoder::FORMAT, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true])
            ->willReturn([$version]);

        static::assertSame([$version], $this->mergeRequests->versions(111, 222));
    }
}
