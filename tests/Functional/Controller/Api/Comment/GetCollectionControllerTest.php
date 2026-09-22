<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\CommentApiFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
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
    public function testAuthenticatedAccessReturnsComments(): void
    {
        $user = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));

        $this->client->loginUser($user);

        $this->client->request(Request::METHOD_GET, '/api/comments');

        self::assertResponseIsSuccessful();
        $data = $this->getResponseArray();
        static::assertCount(1, $data);
        static::assertIsArray($data[0]);
        static::assertSame(CommentApiFixtures::OWN_FINAL, $data[0]['message']);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CommentApiFixtures::class];
    }
}
