<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CommentReply;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CommentReplyApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class GetCommentReplyEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testAuthenticatedAccessReturnsReply(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->request($reply->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains([
            'id'        => $reply->getId(),
            'commentId' => $reply->getComment()->getId(),
            'userId'    => $reply->getUser()->getId(),
            'message'   => CommentReplyApiFixtures::FINAL_REPLY_ONE,
            'tag'       => 'suggestion',
            'createdAt' => '1970-01-01T00:16:40+00:00',
            'updatedAt' => '1970-01-01T00:16:40+00:00',
        ]);
    }

    public function testAnotherUserCanReadFinalReply(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_TWO);
        $this->request($reply->getId());

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['message' => CommentReplyApiFixtures::FINAL_REPLY_TWO]);
    }

    public function testDraftAuthorCanReadOwnReply(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::OTHER_DRAFT_REPLY);
        $this->request($reply->getId(), str_pad(CommentReplyApiFixtures::OTHER_USER_TOKEN, 80, '0'));

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['message' => CommentReplyApiFixtures::OTHER_DRAFT_REPLY]);
    }

    public function testOtherUserCannotReadDraftReply(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::OTHER_DRAFT_REPLY);
        $this->request($reply->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMissingReplyReturns404(): void
    {
        $this->request(999999999);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUnauthenticatedAccessReturns401(): void
    {
        $reply = $this->getReply(CommentReplyApiFixtures::FINAL_REPLY_ONE);
        $this->client->request(Request::METHOD_GET, '/api/comment-replies/' . $reply->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CommentReplyApiFixtures::class, UserAccessTokenFixtures::class];
    }

    private function request(int $id, string $token = UserAccessTokenFixtures::TOKEN_VALUE): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/comment-replies/' . $id,
            ['headers' => ['authorization' => 'Bearer ' . $token]],
        );
    }

    private function getReply(string $message): CommentReply
    {
        return Assert::notNull(self::getService(CommentReplyRepository::class)->findOneBy(['message' => $message]));
    }
}
