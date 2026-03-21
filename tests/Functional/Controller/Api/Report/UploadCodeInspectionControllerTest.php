<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Report;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Utils\Assert;
use Exception;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;

#[CoversNothing]
class UploadCodeInspectionControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testUpload(): void
    {
        // give ADMIN role to user
        $userRepository = self::getService(UserRepository::class);
        $user           = Assert::notNull($userRepository->findOneBy(['name' => 'Sherlock Holmes']))->addRole(Roles::ROLE_ADMIN);
        $userRepository->save($user, true);

        $url     = '/api/report/code-inspection/repository/126d52488ea380618c4a69cf8b250d4bd05de6c7?format=gitlab&identifier=foobar';
        $content = Json::encode(
            [['description' => 'message', 'severity' => 'major', 'location' => ['path' => '/file/path', 'lines' => ['begin' => 20]]]]
        );

        $this->client->request(
            Request::METHOD_POST,
            $url,
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE],
            content: $content
        );
        self::assertResponseIsSuccessful();
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RepositoryFixtures::class, UserFixtures::class, UserAccessTokenFixtures::class];
    }
}
