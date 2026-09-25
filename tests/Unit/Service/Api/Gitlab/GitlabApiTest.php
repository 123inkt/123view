<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(GitlabApi::class)]
class GitlabApiTest extends AbstractTestCase
{
    private GitlabApi $api;

    protected function setUp(): void
    {
        parent::setUp();
        $client     = static::createStub(HttpClientInterface::class);
        $serializer = static::createStub(SerializerInterface::class);
        $this->api  = new GitlabApi($this->logger, $client, $serializer);
    }

    public function testUsers(): void
    {
        static::assertSame($this->api->users(), $this->api->users());
    }

    public function testBranches(): void
    {
        static::assertSame($this->api->branches(), $this->api->branches());
    }

    public function testMergeRequests(): void
    {
        static::assertSame($this->api->mergeRequests(), $this->api->mergeRequests());
    }

    public function testDiscussions(): void
    {
        static::assertSame($this->api->discussions(), $this->api->discussions());
    }
}
