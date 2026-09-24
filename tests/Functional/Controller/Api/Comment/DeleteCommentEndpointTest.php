<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use ApiPlatform\Symfony\Bundle\Test\Response as ApiResponse;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\DeleteCommentApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class DeleteCommentEndpointTest extends AbstractApiTestCase
{
    public function testAuthorDeletesFinalCommentThread(): void
    {
        $comment   = Assert::notNull(
            self::getService(CommentRepository::class)->findOneBy(['message' => DeleteCommentApiFixtures::TARGET_COMMENT_MESSAGE])
        );
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
        $comment  = Assert::notNull(
            self::getService(CommentRepository::class)->findOneBy(['message' => DeleteCommentApiFixtures::TARGET_COMMENT_MESSAGE])
        );
        $response = $this->client->request(Request::METHOD_DELETE, '/api/comments/' . $comment->getId());

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode(), $response->getContent(false));
        self::assertCount(0, $this->dispatchedMessages);

        $this->entityManager?->clear();
        $comment = Assert::notNull(self::getService(CommentRepository::class)->find($comment->getId()));
        self::assertCount(2, self::getService(CommentReplyRepository::class)->findBy(['comment' => $comment]));
    }

    private function delete(int $id): ApiResponse
    {
        return Assert::isInstanceOf(
            $this->client->request(
                Request::METHOD_DELETE,
                '/api/comments/' . $id,
                ['headers' => ['authorization' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE]]
            ),
            ApiResponse::class
        );
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [DeleteCommentApiFixtures::class];
    }
}
