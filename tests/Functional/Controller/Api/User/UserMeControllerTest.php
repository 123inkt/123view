<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\User;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class UserMeControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testGet(): void
    {
        $user = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['name' => 'Sherlock Holmes']));

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/users/me');
        self::assertResponseIsSuccessful();

        $data = $this->getResponseArray();
        static::assertSame(['id' => $user->getId(), 'name' => 'Sherlock Holmes', 'email' => 'sherlock@example.com'], $data);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }
}
