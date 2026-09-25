<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CommentReply;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CommentReplyApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class PostCommentReplyEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testPostsReplyAndDispatchesEvent(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE);
        $this->request($comment->getId(), [
            'message' => '  Please extract this condition.  ',
            'tag'     => CommentTagEnum::Suggestion->value,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $response = Assert::isArray(Json::decode(Assert::notNull($this->client->getResponse())->getContent(), true));
        self::assertSame([
            'id',
            'commentId',
            'userId',
            'message',
            'tag',
            'createdAt',
            'updatedAt',
        ], array_keys($response));
        self::assertSame($comment->getId(), $response['commentId']);
        self::assertSame($this->getCurrentUserId(), $response['userId']);
        self::assertSame('Please extract this condition.', $response['message']);
        self::assertSame(CommentTagEnum::Suggestion->value, $response['tag']);
        self::assertIsString($response['createdAt']);
        self::assertSame($response['createdAt'], $response['updatedAt']);

        $this->entityManager?->clear();
        $reply = Assert::notNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => 'Please extract this condition.']));
        self::assertSame($comment->getId(), $reply->getComment()->getId());
        self::assertSame($this->getCurrentUserId(), $reply->getUser()->getId());
        self::assertSame(CommentTagEnum::Suggestion, $reply->getTag());
        self::assertSame($reply->getCreateTimestamp(), $reply->getUpdateTimestamp());

        $messages = $this->messagesOfType(CommentReplyAdded::class);
        self::assertCount(1, $messages);
        self::assertSame($reply->getComment()->getReview()->getId(), $messages[0]->reviewId);
        self::assertSame($reply->getId(), $messages[0]->commentReplyId);
        self::assertSame($this->getCurrentUserId(), $messages[0]->byUserId);
        self::assertSame($reply->getMessage(), $messages[0]->message);
        self::assertSame($reply->getComment()->getFilePath(), $messages[0]->file);
    }

    public function testRejectsUnknownFields(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE);
        $this->request($comment->getId(), [
            'message'     => 'Spoofed reply',
            'commentId'   => 999,
            'userId'      => 999,
            'createdAt'   => '1970-01-01T00:00:00+00:00',
            'updatedAt'   => '1970-01-01T00:00:00+00:00',
            'externalId'  => 'spoofed',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => 'Spoofed reply']));
        self::assertCount(0, $this->messagesOfType(CommentReplyAdded::class));
    }

    public function testRejectsInvalidMessages(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE);
        foreach ([" \t ", str_repeat('m', 2001)] as $message) {
            $this->request($comment->getId(), ['message' => $message]);

            self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        self::assertNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => " \t "]));
        self::assertNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => str_repeat('m', 2001)]));
        self::assertCount(0, $this->messagesOfType(CommentReplyAdded::class));
    }

    public function testMissingParentReturns404WithoutEvent(): void
    {
        $this->request(999999999, ['message' => 'Reply']);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        self::assertCount(0, $this->messagesOfType(CommentReplyAdded::class));
    }

    public function testRejectsDraftParents(): void
    {
        foreach ([CommentReplyApiFixtures::OWN_DRAFT_COMMENT_MESSAGE, CommentReplyApiFixtures::OTHER_DRAFT_COMMENT_MESSAGE] as $message) {
            $comment = $this->getComment($message);
            $this->request($comment->getId(), ['message' => 'Reply to draft']);

            self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        }

        self::assertCount(0, $this->messagesOfType(CommentReplyAdded::class));
    }

    public function testRequiresAuthentication(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE);
        $this->client->request(
            Request::METHOD_POST,
            '/api/comments/' . $comment->getId() . '/replies',
            [
                'headers' => ['content-type' => 'application/json'],
                'json'    => ['message' => 'Reply'],
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertCount(0, $this->messagesOfType(CommentReplyAdded::class));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(int $commentId, array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/comments/' . $commentId . '/replies',
            [
                'headers' => [
                    'authorization' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE,
                    'content-type'  => 'application/json',
                ],
                'json'    => $payload,
            ],
        );
    }

    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }

    private function getCurrentUserId(): int
    {
        return Assert::notNull($this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE)->getUser())->getId();
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CommentReplyApiFixtures::class, UserAccessTokenFixtures::class];
    }
}
