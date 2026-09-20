<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\CommentApiFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class GetControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testGetOwnFinalComment(): void
    {
        $comment = $this->getComment(CommentApiFixtures::OWN_FINAL);
        $this->client->loginUser($comment->getUser());
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSame(
            [
                'id'              => $comment->getId(),
                'userId'          => $comment->getUser()->getId(),
                'reviewId'        => $comment->getReview()->getId(),
                'message'         => CommentApiFixtures::OWN_FINAL,
                'filepath'        => 'src/Foo.php',
                'line'            => 42,
                'sha'             => 'abc123',
                'state'           => 'open',
                'tag'             => 'suggestion',
                'createTimestamp' => 1000,
                'updateTimestamp' => 2000,
            ],
            $this->getResponseArray(),
        );
    }

    /**
     * @throws Exception
     */
    public function testGetForeignFinalComment(): void
    {
        $comment = $this->getComment(CommentApiFixtures::FOREIGN_FINAL);
        $user    = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSame(CommentApiFixtures::FOREIGN_FINAL, $this->getResponseArray()['message']);
    }

    /**
     * @throws Exception
     */
    public function testGetOwnDraft(): void
    {
        $comment = $this->getComment(CommentApiFixtures::OWN_DRAFT);
        $this->client->loginUser($comment->getUser());
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        static::assertSame(CommentApiFixtures::OWN_DRAFT, $this->getResponseArray()['message']);
    }

    /**
     * @throws Exception
     */
    public function testForeignDraftReturns404(): void
    {
        $comment = $this->getComment(CommentApiFixtures::FOREIGN_DRAFT);
        $user    = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws Exception
     */
    public function testMissingCommentReturns404(): void
    {
        $user = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $this->client->loginUser($user);
        $this->client->request(Request::METHOD_GET, '/api/comments/999999999');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws Exception
     */
    public function testUnauthenticatedReturns401(): void
    {
        $comment = $this->getComment(CommentApiFixtures::OWN_FINAL);
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws Exception
     */
    public function testLegacyFieldsAndOutputBoundary(): void
    {
        $comment = $this->getComment(CommentApiFixtures::LEGACY);
        $this->client->loginUser($comment->getUser());
        $this->client->request(Request::METHOD_GET, '/api/comments/' . $comment->getId());

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = $this->getResponseArray();
        static::assertSame(
            ['id', 'userId', 'reviewId', 'message', 'filepath', 'line', 'sha', 'state', 'tag', 'createTimestamp', 'updateTimestamp'],
            array_keys($data),
        );
        static::assertSame(8, $data['line']);
        static::assertNull($data['sha']);
        static::assertNull($data['tag']);
        static::assertArrayNotHasKey('replies', $data);
        static::assertArrayNotHasKey('lineReference', $data);
        static::assertArrayNotHasKey('notificationStatus', $data);
    }

    /**
     * @throws Exception
     */
    public function testGetOperationIsDocumented(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/docs',
            server: ['HTTP_ACCEPT' => 'application/vnd.openapi+json'],
        );

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertArrayHasKey('/api/comments/{id}', $data['paths']);
        static::assertArrayHasKey('get', $data['paths']['/api/comments/{id}']);
        static::assertArrayHasKey('200', $data['paths']['/api/comments/{id}']['get']['responses']);
        static::assertArrayHasKey('application/json', $data['paths']['/api/comments/{id}']['get']['responses']['200']['content']);
        static::assertArrayHasKey('schema', $data['paths']['/api/comments/{id}']['get']['responses']['200']['content']['application/json']);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CommentApiFixtures::class];
    }

    /**
     * @throws Exception
     */
    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }
}
