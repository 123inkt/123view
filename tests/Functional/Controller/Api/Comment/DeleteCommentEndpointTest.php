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

#[CoversNothing]
class DeleteCommentEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $commentRepository = static::getContainer()->get(CommentRepository::class);
        $targetComment     = Assert::isInstanceOf($commentRepository, CommentRepository::class)->findOneBy(['message' => 'patch author final']);
        $unrelatedComment  = Assert::isInstanceOf($commentRepository, CommentRepository::class)->findOneBy(['message' => 'patch other final']);
        self::assertInstanceOf(Comment::class, $targetComment);
        self::assertInstanceOf(Comment::class, $unrelatedComment);

        $this->addReply($targetComment, 'first target reply');
        $this->addReply($targetComment, 'second target reply');
        $this->addReply($unrelatedComment, 'unrelated reply');

        $this->entityManager?->flush();
    }

    public function testAuthorDeletesFinalCommentThread(): void
    {
        $comment   = Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => 'patch author final']));
        $commentId = $comment->getId();
        $response  = $this->delete($comment->getId());

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertCount(2, $this->messagesOfType(CommentReplyRemoved::class));
        self::assertCount(1, $this->messagesOfType(CommentRemoved::class));

        $this->entityManager?->clear();
        self::assertNull(self::getService(CommentRepository::class)->find($commentId));
    }

    public function testUnauthenticatedRequestReturns401(): void
    {
        $comment  = Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => 'patch author final']));
        $response = $this->client->request(Request::METHOD_DELETE, '/api/comments/' . $comment->getId());

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);
        $this->assertThreadExists($comment);
    }

    private function delete(int $id): ApiResponse
    {
        return Assert::isInstanceOf(
            $this->client->request(
                Request::METHOD_DELETE,
                '/api/comments/' . $id,
                ['headers' => ['authorization' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE],]
            ),
            ApiResponse::class
        );
    }

    private function assertThreadExists(Comment $comment): void
    {
        $commentId = $comment->getId();
        $this->entityManager?->clear();
        $comment = Assert::notNull(self::getService(CommentRepository::class)->find($commentId));
        self::assertCount(2, self::getService(CommentReplyRepository::class)->findBy(['comment' => $comment]));
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

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [PatchCommentApiFixtures::class];
    }
}
