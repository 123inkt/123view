<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use ApiPlatform\Symfony\Bundle\Test\Response as ApiResponse;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\PatchCommentApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversNothing]
class DeleteControllerTest extends AbstractApiTestCase
{
    /** @var list<object> */
    private array $dispatchedMessages = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();

        $bus = static::createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message): Envelope {
            $this->dispatchedMessages[] = $message;

            return new Envelope($message);
        });
        static::getContainer()->set(MessageBusInterface::class, $bus);

        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $targetComment = Assert::isInstanceOf($commentRepository, CommentRepository::class)->findOneBy(['message' => 'patch author final']);
        $unrelatedComment = Assert::isInstanceOf($commentRepository, CommentRepository::class)->findOneBy(['message' => 'patch other final']);
        self::assertInstanceOf(Comment::class, $targetComment);
        self::assertInstanceOf(Comment::class, $unrelatedComment);

        $this->addReply($targetComment, 'first target reply');
        $this->addReply($targetComment, 'second target reply');
        $this->addReply($unrelatedComment, 'unrelated reply');

        $this->entityManager?->flush();
        $this->addMention($targetComment->getId(), $unrelatedComment->getUser()->getId());
        $this->addMention($unrelatedComment->getId(), $targetComment->getUser()->getId());
        self::assertSame(1, $this->countRows('user_mention', 'comment_id', $targetComment->getId()));
        self::assertSame(1, $this->countRows('user_mention', 'comment_id', $unrelatedComment->getId()));
        $this->entityManager?->clear();
    }

    public function testAuthorDeletesFinalCommentThread(): void
    {
        $comment = $this->getComment('patch author final');
        $unrelatedComment = $this->getComment('patch other final');
        $commentId = $comment->getId();
        $unrelatedCommentId = $unrelatedComment->getId();
        self::assertCount(
            1,
            $comment->getMentions(),
        );

        $response = $this->delete($comment->getId());

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent(false));
        self::assertSame('', $response->getContent(false));
        self::assertCount(2, $this->messagesOfType(CommentReplyRemoved::class));
        self::assertCount(
            1,
            $this->messagesOfType(CommentRemoved::class),
            'The Doctrine subscriber must generate exactly one comment-removal message.',
        );

        $this->entityManager?->clear();
        $commentRepository = self::getService(CommentRepository::class);
        $replyRepository = self::getService(CommentReplyRepository::class);

        self::assertNull($commentRepository->find($commentId));
        self::assertSame(0, $this->countRows('comment_reply', 'comment_id', $commentId));
        self::assertSame(0, $this->countRows('user_mention', 'comment_id', $commentId));

        $unrelatedComment = Assert::notNull($commentRepository->find($unrelatedCommentId));
        self::assertSame('patch other final', $unrelatedComment->getMessage());
        self::assertCount(1, $replyRepository->findBy(['comment' => $unrelatedComment]));
        self::assertSame(1, $this->countRows('user_mention', 'comment_id', $unrelatedCommentId));
    }

    public function testAuthorDeletesOwnDraft(): void
    {
        $comment = $this->getComment('patch author draft');
        $commentId = $comment->getId();

        $response = $this->delete($comment->getId());

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent(false));
        self::assertSame('', $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages, 'Draft comment removal events remain suppressed.');
        $this->entityManager?->clear();
        self::assertNull(self::getService(CommentRepository::class)->find($commentId));
        self::assertSame(0, $this->countRows('user_mention', 'comment_id', $commentId));
    }

    public function testAnotherUserCannotDeleteFinalComment(): void
    {
        $comment = $this->getComment('patch author final');

        $response = $this->delete($comment->getId(), $this->otherToken());

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);
        $this->assertThreadExists($comment);
    }

    public function testAuthorCanDeleteFinalComment(): void
    {
        $comment = $this->getComment('patch author final');

        $response = $this->delete($comment->getId());

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent(false));
    }

    public function testAnotherUsersDraftIsHidden(): void
    {
        $comment = $this->getComment('patch other draft');

        $response = $this->delete($comment->getId());

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);
        $commentId = $comment->getId();
        $this->entityManager?->clear();
        self::assertNotNull(self::getService(CommentRepository::class)->find($commentId));
    }

    public function testMissingCommentReturnsNotFound(): void
    {
        $response = $this->delete(987654);

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);
    }

    public function testUnauthenticatedRequestReturns401(): void
    {
        $comment = $this->getComment('patch author final');
        $response = $this->client->request(Request::METHOD_DELETE, '/api/comments/' . $comment->getId());

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);
        $this->assertThreadExists($comment);
    }

    /**
     * @param class-string $messageType
     * @return list<object>
     */
    private function messagesOfType(string $messageType): array
    {
        return array_values(array_filter($this->dispatchedMessages, static fn(object $message): bool => $message instanceof $messageType));
    }

    private function delete(int $id, ?string $token = UserAccessTokenFixtures::TOKEN_VALUE): ApiResponse
    {
        return Assert::isInstanceOf($this->client->request(Request::METHOD_DELETE, '/api/comments/' . $id, [
            'headers' => ['authorization' => 'Bearer ' . $token],
        ]), ApiResponse::class);
    }

    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }

    private function assertThreadExists(Comment $comment): void
    {
        $commentId = $comment->getId();
        $this->entityManager?->clear();
        $comment = Assert::notNull(self::getService(CommentRepository::class)->find($commentId));
        self::assertCount(2, self::getService(CommentReplyRepository::class)->findBy(['comment' => $comment]));
        self::assertSame(
            1,
            $this->countRows('user_mention', 'comment_id', $commentId),
        );
    }

    private function addReply(Comment $comment, string $message): void
    {
        $reply = new CommentReply();
        $reply->setMessage($message);
        $reply->setTag(null);
        $reply->setComment($comment);
        $reply->setUser($comment->getUser());
        $reply->setCreateTimestamp(1_000);
        $reply->setUpdateTimestamp(2_000);
        $comment->getReplies()->add($reply);
        $this->entityManager?->persist($reply);
    }

    private function addMention(int $commentId, int $userId): void
    {
        $this->entityManager?->getConnection()->insert('user_mention', [
            'comment_id' => $commentId,
            'user_id'    => $userId,
        ]);
    }

    private function countRows(string $table, string $column, int $commentId): int
    {
        $count = $this->entityManager?->getConnection()->fetchOne(
            sprintf('SELECT COUNT(*) FROM %s WHERE %s = ?', $table, $column),
            [$commentId],
        );

        return Assert::integer($count);
    }

    private function otherToken(): string
    {
        return str_pad(PatchCommentApiFixtures::OTHER_USER_TOKEN, 80, '0');
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [PatchCommentApiFixtures::class];
    }
}
