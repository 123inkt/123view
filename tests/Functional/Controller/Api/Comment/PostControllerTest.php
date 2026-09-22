<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversNothing]
class PostControllerTest extends AbstractApiTestCase
{
    /** @var list<Revision> */
    private array $revisions;

    /** @var list<object> */
    private array $dispatchedMessages = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisions = [
            new Revision()->setRepository(new Repository())->setCommitHash('0123456789abcdef0123456789abcdef01234567'),
        ];
        $revisionService = static::createStub(CodeReviewRevisionService::class);
        $revisionService->method('getRevisions')->willReturnCallback(fn(): array => $this->revisions);
        static::getContainer()->set(CodeReviewRevisionService::class, $revisionService);

        $diffService = static::createStub(CodeReviewDiffService::class);
        $diffService->method('getDiff')->willReturn([
            $this->createDiffFile('src/Foo.php', 'src/Foo.php', [
                $this->createLine(DiffLine::STATE_CHANGED, 41, 42),
                $this->createLine(DiffLine::STATE_REMOVED, 43, null),
            ]),
        ]);
        static::getContainer()->set(CodeReviewDiffService::class, $diffService);

        $bus = static::createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(
            function (object $message): Envelope {
                $this->dispatchedMessages[] = $message;

                return new Envelope($message);
            },
        );
        static::getContainer()->set(MessageBusInterface::class, $bus);
    }

    public function testPostsComment(): void
    {
        $this->request(
            [
                'message'  => '  Please extract this condition.  ',
                'filepath' => 'src/Foo.php',
                'line'     => 42,
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertJsonContains([
            'userId'  => $this->getCurrentUserId(),
            'reviewId' => CodeReviewFixtures::REVIEW_ID,
            'message' => 'Please extract this condition.',
            'filepath' => 'src/Foo.php',
            'line'    => 42,
            'sha'     => '0123456789abcdef0123456789abcdef01234567',
            'state'   => 'open',
            'tag'     => null,
        ]);

        $this->entityManager?->clear();
        $comment = Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => 'Please extract this condition.']));
        self::assertSame($this->getCurrentUserId(), $comment->getUser()->getId());
        self::assertSame(CodeReviewFixtures::REVIEW_ID, $comment->getReview()->getId());
        self::assertSame('open', $comment->getState()->value);
        self::assertSame('final', $comment->getType()->value);
        self::assertSame('0123456789abcdef0123456789abcdef01234567', $comment->getLineReference()->headSha);
        self::assertSame($comment->getCreateTimestamp(), $comment->getUpdateTimestamp());
        self::assertSame(7, $comment->getNotificationStatus()->getStatus());
        self::assertCount(1, $this->dispatchedMessages);
        self::assertInstanceOf(CommentAdded::class, $this->dispatchedMessages[0]);
    }

    public function testPostRejectsUnknownFields(): void
    {
        $this->request([
            'message'  => 'Comment',
            'filepath' => 'src/Foo.php',
            'line'     => 42,
            'reviewId' => 999,
            'userId'   => 999,
            'state'    => 'resolved',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertCount(0, self::getService(CommentRepository::class)->findBy(['message' => 'Comment']));
    }

    public function testPostRequiresAuthentication(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/code-reviews/' . CodeReviewFixtures::REVIEW_ID . '/comments',
            ['headers' => ['content-type' => 'application/json'], 'json' => [
                'message' => 'Comment', 'filepath' => 'src/Foo.php', 'line' => 42,
            ]],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(array $payload, int $reviewId = CodeReviewFixtures::REVIEW_ID): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/code-reviews/' . $reviewId . '/comments',
            [
                'headers' => [
                    'authorization' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE,
                    'content-type'  => 'application/json',
                ],
                'json' => $payload,
            ],
        );
    }

    /**
     * @param array<int, DiffLine> $lines
     */
    private function createDiffFile(string $oldPath, ?string $newPath, array $lines): DiffFile
    {
        $block        = new DiffBlock();
        $block->lines = $lines;
        $file                 = new DiffFile();
        $file->filePathBefore = $oldPath;
        $file->filePathAfter  = $newPath;
        $file->addBlock($block);

        return $file;
    }

    private function createLine(int $state, ?int $lineNumberBefore, ?int $lineNumberAfter): DiffLine
    {
        $line                   = new DiffLine($state, []);
        $line->lineNumberBefore = $lineNumberBefore;
        $line->lineNumberAfter  = $lineNumberAfter;

        return $line;
    }

    private function getCurrentUserId(): int
    {
        $user = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));

        return $user->getId();
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CodeReviewFixtures::class, UserFixtures::class, UserAccessTokenFixtures::class];
    }
}
