<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ExternalTool\Gitlab;

use DR\GitCommitNotification\ExternalTool\Gitlab\GitlabApi;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ExternalTool\Gitlab\GitlabApi
 * @covers ::__construct
 */
class GitlabApiTest extends AbstractTestCase
{
    /** @var MockObject|HttpClientInterface */
    private HttpClientInterface $client;
    private GitlabApi         $api;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->api    = new GitlabApi($this->client, new ArrayAdapter());
    }

    /**
     * @covers ::getBranchUrl
     * @throws Throwable
     */
    public function testGetBranchUrl(): void
    {
        $projectId = 123;
        $remoteRef = "foobar";

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['web_url' => 'https://gitlab.example.com']);

        // should call http client once.
        $this->client->expects(static::once())
            ->method('request')
            ->with(
                'GET',
                'projects/123/repository/branches/foobar'
            )->willReturn($response);

        // first time should call http-client
        static::assertSame('https://gitlab.example.com', $this->api->getBranchUrl($projectId, $remoteRef));

        // second time is from cache
        static::assertSame('https://gitlab.example.com', $this->api->getBranchUrl($projectId, $remoteRef));
    }

    /**
     * @covers ::getMergeRequestUrl
     * @throws Throwable
     */
    public function testGetMergeRequestUrl(): void
    {
        $projectId = 123;
        $remoteRef = "foobar";

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([['web_url' => 'https://gitlab.example.com']]);

        // should call http client once.
        $this->client->expects(static::once())
            ->method('request')
            ->with(
                'GET',
                'projects/123/merge_requests'
            )->willReturn($response);

        // first time should call http-client
        static::assertSame('https://gitlab.example.com', $this->api->getMergeRequestUrl($projectId, $remoteRef));

        // second time is from cache
        static::assertSame('https://gitlab.example.com', $this->api->getMergeRequestUrl($projectId, $remoteRef));
    }
}
