<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\CommentApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[CoversNothing]
class GetCommentEndpointTest extends AbstractApiTestCase
{
    /**
     * @throws Throwable
     */
    public function testGetComment(): void
    {
        $comment = $this->getComment();
        $this->client->request(
            Request::METHOD_GET,
            '/api/comments/' . $comment->getId(),
            ['headers' => ['authorization' => ['Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE]]],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertJsonContains(
            [
                'id'        => $comment->getId(),
                'userId'    => $comment->getUser()->getId(),
                'reviewId'  => $comment->getReview()->getId(),
                'message'   => CommentApiFixtures::OWN_FINAL,
                'filepath'  => 'src/Foo.php',
                'line'      => 42,
                'sha'       => 'abc123',
                'state'     => 'open',
                'tag'       => 'suggestion',
                'createdAt' => '1970-01-01T00:16:40+00:00',
                'updatedAt' => '1970-01-01T00:33:20+00:00',
            ],
        );
    }

    /**
     * @throws Throwable
     */
    public function testUnauthenticatedReturns401(): void
    {
        $comment = $this->getComment();
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CommentApiFixtures::class, UserAccessTokenFixtures::class];
    }

    /**
     * @throws Throwable
     */
    private function getComment(): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => CommentApiFixtures::OWN_FINAL]));
    }
}
