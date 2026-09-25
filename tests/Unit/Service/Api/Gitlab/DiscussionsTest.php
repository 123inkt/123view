<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

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

    /**
     * @throws Throwable
     */
    public function testGetDiscussions(): void
    {
        $discussionA = ['id' => 333, 'notes' => [['id' => 444]]];
        $discussionB = ['id' => 555, 'notes' => [['id' => 666]]];

        $response = static::createStub(ResponseInterface::class);
        $response->method('getHeaders')->willReturn(['x-next-page' => ['2']], ['x-next-page' => []]);
        $response->method('toArray')->willReturn([$discussionA], [$discussionB]);

        $this->client->expects($this->exactly(2))
            ->method('request')
            ->with(
                ...consecutive(
                    [
                        'GET',
                        'projects/111/merge_requests/222/discussions',
                        ['query' => ['per_page' => 20, 'page' => 1]]
                    ],
                    [
                        'GET',
                        'projects/111/merge_requests/222/discussions',
                        ['query' => ['per_page' => 20, 'page' => 2]]
                    ]
                )
            )->willReturn($response);

        $discussions = [];
        foreach ($this->discussions->getDiscussions(111, 222) as $discussion) {
            $discussions[] = $discussion;
        }
        static::assertSame([$discussionA, $discussionB], $discussions);
    }

    /**
     * @throws Throwable
     */
    public function testCreateDiscussion(): void
    {
        $position               = new Position();
        $position->positionType = 'text';
        $position->baseSha      = 'base';
        $position->startSha     = 'start';
        $position->headSha      = 'head';
        $position->oldPath      = 'old';
        $position->oldLine      = 1;

        $response = static::createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['id' => 333, 'notes' => [['id' => 444]]]);

        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'projects/111/merge_requests/222/discussions',
                [
                    'body' => [
                        'position[position_type]' => 'text',
                        'position[base_sha]'      => 'base',
                        'position[head_sha]'      => 'head',
                        'position[start_sha]'     => 'start',
                        'position[old_path]'      => 'old',
                        'position[old_line]'      => 1,
                        'body'                    => 'body'
                    ]
                ]
            )->willReturn($response);

        $referenceId = $this->discussions->createDiscussion(111, 222, $position, 'body');
        static::assertSame('222:333:444', $referenceId);
    }

    /**
     * @throws Throwable
     */
    public function testCreateNote(): void
    {
        $response = static::createStub(ResponseInterface::class);
        $response->method('toArray')->willReturn(['id' => 444]);

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', 'projects/111/merge_requests/222/discussions/333/notes', ['query' => ['body' => 'body']])
            ->willReturn($response);

        $extReferenceId = $this->discussions->createNote(111, 222, '333', 'body');
        static::assertSame('222:333:444', $extReferenceId);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateNote(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('PUT', 'projects/111/merge_requests/222/discussions/333/notes/444', ['query' => ['body' => 'body']]);

        $this->discussions->updateNote(111, 222, '333', '444', 'body');
    }

    /**
     * @throws Throwable
     */
    public function testResolve(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('PUT', 'projects/111/merge_requests/222/discussions/333', ['query' => ['resolved' => 'true']]);

        $this->discussions->resolve(111, 222, '333');
    }

    /**
     * @throws Throwable
     */
    public function testDeleteNote(): void
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('DELETE', 'projects/111/merge_requests/222/discussions/333/notes/444');

        $this->discussions->deleteNote(111, 222, '333', '444');
    }
}
