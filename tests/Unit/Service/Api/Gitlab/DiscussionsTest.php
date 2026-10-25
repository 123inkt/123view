<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Discussion;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(Discussions::class)]
class DiscussionsTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $client;
    private SerializerInterface&MockObject  $serializer;
    private Discussions                     $discussions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client      = $this->createMock(HttpClientInterface::class);
        $this->serializer  = $this->createMock(SerializerInterface::class);
        $this->discussions = new Discussions($this->client, $this->serializer);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiscussions(): void
    {
        $discussionA     = new Discussion();
        $discussionA->id = '333';
        $discussionB     = new Discussion();
        $discussionB->id = '555';

        $response = static::createStub(ResponseInterface::class);
        $response->method('getHeaders')->willReturn(['x-next-page' => ['2']], ['x-next-page' => []]);
        $response->method('getContent')->willReturn('json-a', 'json-b');

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
        $this->serializer->expects($this->exactly(2))
            ->method('deserialize')
            ->with(...consecutive(
                ['json-a', Discussion::class . '[]', JsonEncoder::FORMAT, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]],
                ['json-b', Discussion::class . '[]', JsonEncoder::FORMAT, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]],
            ))
            ->willReturn([$discussionA], [$discussionB]);

        $discussions = [];
        foreach ($this->discussions->getDiscussions(111, 222) as $discussion) {
            $discussions[] = $discussion;
        }
        static::assertSame([$discussionA, $discussionB], $discussions);
    }

    /**
     * @throws Throwable
     */
    public function testGetDiscussion(): void
    {
        $discussion     = new Discussion();
        $discussion->id = '333';
        $response       = static::createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->method('getContent')->willReturn('json');

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/111/merge_requests/222/discussions/333')
            ->willReturn($response);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('json', Discussion::class, JsonEncoder::FORMAT, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true])
            ->willReturn($discussion);

        static::assertSame($discussion, $this->discussions->getDiscussion(111, 222, '333'));
    }

    /**
     * @throws Throwable
     */
    public function testGetDiscussionNotFound(): void
    {
        $response = static::createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_NOT_FOUND);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'projects/111/merge_requests/222/discussions/333')
            ->willReturn($response);
        $this->serializer->expects($this->never())->method('deserialize');

        static::assertNull($this->discussions->getDiscussion(111, 222, '333'));
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
        $this->serializer->expects($this->never())->method('deserialize');

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
        $this->serializer->expects($this->never())->method('deserialize');

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
        $this->serializer->expects($this->never())->method('deserialize');

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
        $this->serializer->expects($this->never())->method('deserialize');

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
        $this->serializer->expects($this->never())->method('deserialize');

        $this->discussions->deleteNote(111, 222, '333', '444');
    }
}
