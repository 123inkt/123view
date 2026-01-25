<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api;

use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class AuthenticationTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testAuthenticatedSuccess(): void
    {
        $user  = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['name' => 'Sherlock Holmes']));
        $token = Assert::notNull(self::getService(UserAccessTokenRepository::class)->findOneBy(['user' => $user]));

        $this->client->request(Request::METHOD_GET, '/api/users/me', server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->getToken()]);
        self::assertResponseIsSuccessful();

        $data = $this->getResponseArray();
        static::assertSame(['id' => $user->getId(), 'name' => $user->getName(), 'email' => $user->getEmail()], $data);
    }

    /**
     * @throws Exception
     */
    public function testAuthenticatedFailure(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/api/users/me',
            server: ['HTTP_AUTHORIZATION' => 'Bearer foobar']
        );
        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserAccessTokenFixtures::class];
    }
}
