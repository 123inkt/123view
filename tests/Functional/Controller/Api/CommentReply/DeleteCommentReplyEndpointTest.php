<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CommentReply;

use ApiPlatform\Test\Response as ApiResponse;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentReplyRemoved;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CommentReplyApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class DeleteCommentReplyEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testAuthorDeletesReplyDispatchesEvent(): void
    {
        $reply       = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $replyId     = $reply->getId();
        $comment     = $reply->getComment();
        $reviewId    = $comment->getReview()->getId();
        $commentId   = $comment->getId();
        $ownerUserId = $reply->getUser()->getId();
        $message     = $reply->getMessage();

        $response = $this->delete($replyId);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame('', Assert::notNull($this->client->getResponse())->getContent(false));

        $messages = $this->messagesOfType(CommentReplyRemoved::class);
        self::assertCount(1, $messages);
        self::assertSame($reviewId, $messages[0]->reviewId);
        self::assertSame($commentId, $messages[0]->commentId);
        self::assertSame($replyId, $messages[0]->commentReplyId);
        self::assertSame($ownerUserId, $messages[0]->ownerUserId);
        self::assertSame($ownerUserId, $messages[0]->byUserId);
        self::assertSame($message, $messages[0]->message);
        self::assertNull($messages[0]->extReferenceId);

        $this->entityManager?->clear();
        self::assertNull(self::getService(CommentReplyRepository::class)->find($replyId));
    }

    public function testOtherAuthorForbiddenNoMutation(): void
    {
        $reply           = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_TWO);
        $replyId         = $reply->getId();
        $originalMessage = $reply->getMessage();

        $this->delete($replyId);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertCount(0, $this->messagesOfType(CommentReplyRemoved::class));
        self::assertSame($originalMessage, $this->reload($reply)->getMessage());
    }

    public function testHiddenReply404NoMutation(): void
    {
        $reply           = $this->getReply(CommentReplyApiFixtures::OTHER_DRAFT_REPLY);
        $replyId         = $reply->getId();
        $originalMessage = $reply->getMessage();

        $this->delete($replyId);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertCount(0, $this->messagesOfType(CommentReplyRemoved::class));
        self::assertSame($originalMessage, $this->reload($reply)->getMessage());
    }

    public function testMissingReplyReturns404WithoutEvent(): void
    {
        $this->delete(999_999_999);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertCount(0, $this->messagesOfType(CommentReplyRemoved::class));
    }

    public function testUnauthenticated401NoMutation(): void
    {
        $reply   = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $replyId = $reply->getId();
        $this->client->request(Request::METHOD_DELETE, '/api/comment-replies/' . $replyId);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertCount(0, $this->dispatchedMessages);
        self::assertSame($reply->getMessage(), $this->reload($reply)->getMessage());
    }

    private function delete(int $id): ApiResponse
    {
        return Assert::isInstanceOf(
            $this->client->request(
                Request::METHOD_DELETE,
                '/api/comment-replies/' . $id,
                ['headers' => ['authorization' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE]],
            ),
            ApiResponse::class,
        );
    }

    private function getReply(string $message): CommentReply
    {
        return Assert::notNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => $message]));
    }

    private function reload(CommentReply $reply): CommentReply
    {
        $this->entityManager?->clear();

        return Assert::notNull(self::getService(CommentReplyRepository::class)->find($reply->getId()));
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CommentReplyApiFixtures::class, UserAccessTokenFixtures::class];
    }
}
