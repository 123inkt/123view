<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Utils\Assert;

class UserAccessTokenFixtures extends Fixture implements DependentFixtureInterface
{
    public const TOKEN_VALUE = 'token';

    public function load(ObjectManager $manager): void
    {
        $user = Assert::notNull($manager->getRepository(User::class)->findOneBy(['name' => 'Sherlock Holmes']));

        $token = new UserAccessToken();
        $token->setUser($user);
        $token->setName('name');
        $token->setToken(self::TOKEN_VALUE);
        $token->setUsages(5);
        $token->setCreateTimestamp(123456789);
        $token->setUseTimestamp(123456789);

        $manager->persist($token);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
