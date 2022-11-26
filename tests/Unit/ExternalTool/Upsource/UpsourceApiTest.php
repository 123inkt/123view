<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ExternalTool\Upsource;

use DR\GitCommitNotification\ExternalTool\Upsource\UpsourceApi;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ExternalTool\Upsource\UpsourceApi
 * @covers ::__construct
 */
class UpsourceApiTest extends AbstractTestCase
{
    /** @var MockObject|HttpClientInterface */
    private HttpClientInterface $client;
    private UpsourceApi         $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->api    = new UpsourceApi($this->client, new ArrayAdapter());
    }

    /**
     * @covers ::getReviewId
     * @throws Throwable
     */
    public function testGetReviewId(): void
    {
        $projectId = 'myProject';
        $subject   = "foobar";

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['result' => ['reviews' => [['reviewId' => ['reviewId' => 'cr-12345']]]]]);

        // should call http client once.
        $this->client->expects(static::once())
            ->method('request')
            ->with(
                'POST',
                'getReviews',
                static::callback(
                    static fn(array $arg): bool => $arg === ['json' => ['projectId' => $projectId, 'query' => '"foobar"', 'limit' => 1]]
                )
            )->willReturn($response);

        // first time should call http-client
        static::assertSame('cr-12345', $this->api->getReviewId($projectId, $subject));

        // second time is from cache
        static::assertSame('cr-12345', $this->api->getReviewId($projectId, $subject));
    }
}
