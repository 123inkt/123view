<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[CoversClass(Discussions::class)]
class DiscussionsTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $client;
    private Discussions                    $discussions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client      = $this->createMock(HttpClientInterface::class);
        $this->discussions = new Discussions($this->client);
    }

    public function testCreate(): void
    {
    }

    /**
     * @throws Throwable
     */
    public function testUpdate(): void
    {
        $this->client->expects(self::once())
            ->method('request')
            ->with('PUT', 'projects/111/merge_requests/222/discussions/333/notes/444', ['query' => ['body' => 'body']]);

        $this->discussions->update(111, 222, '333', '444', 'body');
    }

    /**
     * @throws Throwable
     */
    public function testResolve(): void
    {
        $this->client->expects(self::once())
            ->method('request')
            ->with('PUT', 'projects/111/merge_requests/222/discussions/333', ['query' => ['resolved' => 'true']]);

        $this->discussions->resolve(111, 222, '333');
    }

    /**
     * @throws Throwable
     */
    public function testDelete(): void
    {
        $this->client->expects(self::once())
            ->method('request')
            ->with('DELETE', 'projects/111/merge_requests/222/discussions/333/notes/444');

        $this->discussions->delete(111, 222, '333', '444');
    }
}
