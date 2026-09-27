<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CommentReply;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CommentReplyApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class PatchCommentReplyEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testAuthorUpdatesMessageAndTag(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->request($reply->getId(), [
            'message' => '  Updated reply  ',
            'tag'     => CommentTagEnum::ChangeRequest->value,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = Assert::isArray(Json::decode($this->getResponseContent(), true));
        self::assertSame([
            'id',
            'commentId',
            'userId',
            'message',
            'tag',
            'createdAt',
            'updatedAt',
        ], array_keys(Assert::isArray($response)));
        self::assertSame('Updated reply', $response['message']);
        self::assertSame(CommentTagEnum::ChangeRequest->value, $response['tag']);

        $this->entityManager?->clear();
        $updatedReply = $this->getReply('Updated reply');
        self::assertSame(CommentTagEnum::ChangeRequest, $updatedReply->getTag());
        self::assertGreaterThan(1_000, $updatedReply->getUpdateTimestamp());

        $messages = $this->messagesOfType(CommentReplyUpdated::class);
        self::assertCount(1, $messages);
        self::assertSame($updatedReply->getComment()->getReview()->getId(), $messages[0]->reviewId);
        self::assertSame($updatedReply->getId(), $messages[0]->commentReplyId);
        self::assertSame($updatedReply->getUser()->getId(), $messages[0]->byUserId);
        self::assertSame(CommentReplyApiFixtures::FINAL_REPLY_ONE, $messages[0]->originalComment);
    }

    public function testTagOnlySuppressesEvent(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->request($reply->getId(), ['tag' => null]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains([
            'message' => CommentReplyApiFixtures::FINAL_REPLY_ONE,
            'tag'     => null,
        ]);
        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
    }

    public function testUnchangedMessageSuppressesEvent(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->request($reply->getId(), ['message' => '  ' . CommentReplyApiFixtures::FINAL_REPLY_ONE . '  ']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains(['message' => CommentReplyApiFixtures::FINAL_REPLY_ONE]);
        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
    }

    public function testRejectsEmptyInvalidAndUnknownInput(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);

        foreach ([[], ['message' => " \t "], ['message' => str_repeat('m', 2_001)]] as $payload) {
            $this->request($reply->getId(), $payload);
            self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->request($reply->getId(), ['message' => 'Updated', 'commentId' => 999, 'userId' => 999]);
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
        self::assertSame(CommentReplyApiFixtures::FINAL_REPLY_ONE, $this->reload($reply)->getMessage());
    }

    public function testOtherAuthorIsForbidden(): void
    {
        $reply           = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_TWO);
        $originalMessage = $reply->getMessage();
        $originalTag     = $reply->getTag();
        $originalUpdated = $reply->getUpdateTimestamp();

        $this->request($reply->getId(), ['message' => 'Unauthorized edit', 'tag' => 'suggestion']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $reply = $this->reload($reply);
        self::assertSame($originalMessage, $reply->getMessage());
        self::assertSame($originalTag, $reply->getTag());
        self::assertSame($originalUpdated, $reply->getUpdateTimestamp());
        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
    }

    public function testHiddenOrMissingReturns404(): void
    {
        $hiddenReply = $this->getReply(CommentReplyApiFixtures::OTHER_DRAFT_REPLY);
        $this->request($hiddenReply->getId(), ['message' => 'Hidden edit']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->request(999_999_999, ['message' => 'Missing edit']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
        self::assertSame(CommentReplyApiFixtures::OTHER_DRAFT_REPLY, $this->reload($hiddenReply)->getMessage());
    }

    public function testRequiresAuthentication(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->client->request(
            Request::METHOD_PATCH,
            '/api/comment-replies/' . $reply->getId(),
            [
                'headers' => ['content-type' => 'application/merge-patch+json'],
                'json'    => ['message' => 'Unauthorized'],
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertCount(0, $this->messagesOfType(CommentReplyUpdated::class));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(int $id, array $payload, string $token = UserAccessTokenFixtures::TOKEN_VALUE): void
    {
        $this->client->request(
            Request::METHOD_PATCH,
            '/api/comment-replies/' . $id,
            [
                'headers' => [
                    'authorization' => 'Bearer ' . $token,
                    'content-type'  => 'application/merge-patch+json',
                ],
                'json' => $payload,
            ],
        );
    }

    private function getResponseContent(): string
    {
        return Assert::notNull($this->client->getResponse())->getContent(false);
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
