<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\CommentApiFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class GetCollectionControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testUnauthenticatedAccessReturns401(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/comments');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws Exception
     */
    public function testDefaultResultsAreOldestFirst(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertCount(30, $data);
        static::assertSame(CommentApiFixtures::OWN_FINAL, $data[0]['message']);
        static::assertSame('api pagination final 23', $data[29]['message']);
    }

    /**
     * @throws Exception
     */
    public function testEqualTimestampsUseIdAsTieBreaker(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?itemsPerPage=10');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertSame('api equal timestamp first', $data[4]['message']);
        static::assertSame('api equal timestamp second', $data[5]['message']);
        static::assertLessThan($data[5]['id'], $data[4]['id']);
    }

    /**
     * @throws Exception
     */
    public function testDescendingCreateTimestamp(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?order[createTimestamp]=desc&itemsPerPage=100');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertSame('api pagination final 29', $data[0]['message']);
        static::assertSame(CommentApiFixtures::OWN_FINAL, $data[count($data) - 1]['message']);
    }

    /**
     * @throws Exception
     */
    #[DataProvider('allowedOrderProperties')]
    public function testEveryAllowedOrderPropertyIsAccepted(string $property): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?order[' . $property . ']=asc&itemsPerPage=2');

        self::assertResponseIsSuccessful();
        static::assertCount(2, $this->getResponseArray(), $property);
    }

    /**
     * @throws Exception
     */
    public function testComposedFiltersUseAndSemantics(): void
    {
        $user   = $this->getUser('sherlock@example.com');
        $review = $this->getComment(CommentApiFixtures::OWN_FINAL)->getReview();
        $this->client->loginUser($user);
        $this->client->request(
            Request::METHOD_GET,
            '/api/comments?user.id=' . $user->getId() . '&review.id=' . $review->getId() . '&exact[filepath]=src/Foo.php',
        );

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertCount(1, $data);
        static::assertSame(CommentApiFixtures::OWN_FINAL, $data[0]['message']);
    }

    /**
     * @throws Exception
     */
    public function testFilepathIsExact(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?exact[filepath]=src/Foo');

        self::assertResponseIsSuccessful();
        static::assertCount(0, $this->getResponseArray());
    }

    /**
     * @throws Exception
     */
    public function testPageAndItemsPerPageAreHonored(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?page=2&itemsPerPage=2');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertCount(2, $data);
        static::assertSame(CommentApiFixtures::OWN_DRAFT, $data[0]['message']);
        static::assertSame(CommentApiFixtures::LEGACY, $data[1]['message']);
    }

    /**
     * @throws Exception
     */
    public function testItemsPerPageAboveMaximumIsCapped(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?itemsPerPage=101');

        self::assertResponseIsSuccessful();
        static::assertCount(36, $this->getResponseArray());
    }

    /**
     * @throws Exception
     */
    public function testPaginationCannotBeDisabled(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?pagination=false');

        self::assertResponseIsSuccessful();
        static::assertCount(30, $this->getResponseArray());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allowedOrderProperties(): array
    {
        return [
            'id'              => ['id'],
            'user'            => ['user.id'],
            'review'          => ['review.id'],
            'filepath'        => ['filepath'],
            'state'           => ['state'],
            'createTimestamp' => ['createTimestamp'],
            'updateTimestamp' => ['updateTimestamp'],
        ];
    }

    /**
     * @throws Exception
     */
    public function testVisibleCommentsAreIncluded(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?itemsPerPage=100');

        self::assertResponseIsSuccessful();
        $messages = array_column($this->getResponseArray(), 'message');
        static::assertContains(CommentApiFixtures::FOREIGN_FINAL, $messages);
        static::assertContains(CommentApiFixtures::OWN_DRAFT, $messages);
        static::assertNotContains(CommentApiFixtures::FOREIGN_DRAFT, $messages);
    }

    /**
     * @throws Exception
     */
    public function testForeignDraftIsHiddenByFilters(): void
    {
        $viewer = $this->getUser('watson@example.com');
        $review = $this->getComment(CommentApiFixtures::FOREIGN_DRAFT)->getReview();
        $this->client->loginUser($this->getUser('sherlock@example.com'));
        $this->client->request(
            Request::METHOD_GET,
            '/api/comments?user.id=' . $viewer->getId() . '&review.id=' . $review->getId() . '&exact[filepath]=src/ForeignDraft.php',
        );

        self::assertResponseIsSuccessful();
        static::assertCount(0, $this->getResponseArray());
    }

    /**
     * @throws Exception
     */
    public function testCollectionUsesCompactCommentOutput(): void
    {
        $this->loginAsSherlock();
        $this->client->request(Request::METHOD_GET, '/api/comments?itemsPerPage=1');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertSame(
            ['id', 'userId', 'reviewId', 'message', 'filepath', 'line', 'sha', 'state', 'tag', 'createTimestamp', 'updateTimestamp'],
            array_keys($data[0]),
        );
        static::assertArrayNotHasKey('replies', $data[0]);
        static::assertArrayNotHasKey('lineReference', $data[0]);
    }

    /**
     * @throws Exception
     */
    public function testCollectionOperationIsDocumented(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/docs',
            server: ['HTTP_ACCEPT' => 'application/vnd.openapi+json'],
        );

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        $operation = $data['paths']['/api/comments']['get'];
        static::assertArrayHasKey('200', $operation['responses']);
        $parameterNames = array_column($operation['parameters'], 'name');
        static::assertContains('user.id', $parameterNames);
        static::assertContains('review.id', $parameterNames);
        static::assertContains('exact[filepath]', $parameterNames);
        static::assertContains('order[filepath]', $parameterNames);
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
    private function loginAsSherlock(): void
    {
        $this->client->loginUser($this->getUser('sherlock@example.com'));
    }

    /**
     * @throws Exception
     */
    private function getUser(string $email): User
    {
        return Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => $email]));
    }

    /**
     * @throws Exception
     */
    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }
}
