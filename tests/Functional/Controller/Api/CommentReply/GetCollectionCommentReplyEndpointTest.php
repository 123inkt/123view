<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CommentReply;

use DR\Review\Entity\Review\Comment;
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
class GetCollectionCommentReplyEndpointTest extends AbstractApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testUnauthenticatedAccessReturns401(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/comment-replies');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAuthenticatedReturnsRepliesInOrder(): void
    {
        $this->request();

        self::assertResponseIsSuccessful();
        $data = $this->getResponseData();
        static::assertSame([
            CommentReplyApiFixtures::FINAL_REPLY_ONE,
            CommentReplyApiFixtures::FINAL_REPLY_TWO,
            CommentReplyApiFixtures::OWN_DRAFT_REPLY,
        ], array_column($data, 'message'));
        static::assertSame([
            'id',
            'commentId',
            'userId',
            'message',
            'tag',
            'createdAt',
            'updatedAt',
        ], array_keys($data[0]));
    }

    public function testFiltersByExactParentCommentId(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::FINAL_COMMENT_MESSAGE);
        $this->request('?comment.id=' . $comment->getId());

        self::assertResponseIsSuccessful();
        static::assertSame(
            [CommentReplyApiFixtures::FINAL_REPLY_ONE, CommentReplyApiFixtures::FINAL_REPLY_TWO],
            array_column($this->getResponseData(), 'message'),
        );
    }

    public function testPaginationFollowsVisibility(): void
    {
        $this->request('?itemsPerPage=1&page=2');

        self::assertResponseIsSuccessful();
        static::assertSame([CommentReplyApiFixtures::FINAL_REPLY_TWO], array_column($this->getResponseData(), 'message'));
    }

    public function testOwnDraftRepliesAreVisible(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::OWN_DRAFT_COMMENT_MESSAGE);
        $this->request('?comment.id=' . $comment->getId());

        self::assertResponseIsSuccessful();
        static::assertSame([CommentReplyApiFixtures::OWN_DRAFT_REPLY], array_column($this->getResponseData(), 'message'));
    }

    public function testHiddenDraftsDoNotAffectPages(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::OTHER_DRAFT_COMMENT_MESSAGE);
        $this->request('?comment.id=' . $comment->getId());

        self::assertResponseIsSuccessful();
        static::assertSame([], $this->getResponseData());

        $this->request('?itemsPerPage=1&page=4');
        self::assertResponseIsSuccessful();
        static::assertSame([], $this->getResponseData());
    }

    public function testDraftAuthorCanSeeOwnDraftReplies(): void
    {
        $comment = $this->getComment(CommentReplyApiFixtures::OTHER_DRAFT_COMMENT_MESSAGE);
        $this->request('?comment.id=' . $comment->getId(), str_pad(CommentReplyApiFixtures::OTHER_USER_TOKEN, 80, '0'));

        self::assertResponseIsSuccessful();
        static::assertSame([CommentReplyApiFixtures::OTHER_DRAFT_REPLY], array_column($this->getResponseData(), 'message'));
    }

    private function request(string $query = '', string $token = UserAccessTokenFixtures::TOKEN_VALUE): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/comment-replies' . $query,
            ['headers' => ['authorization' => 'Bearer ' . $token]],
        );
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function getResponseData(): array
    {
        $response = Assert::notNull($this->client->getResponse());
        $decoded  = Assert::isArray(Json::decode($response->getContent(), true));
        $items    = Assert::isList($decoded);

        return array_map(static fn(mixed $item): array => Assert::isArray($item), $items);
    }

    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CommentReplyApiFixtures::class, UserAccessTokenFixtures::class];
    }
}
