<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Security\Role\Roles;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('Sherlock Holmes');
        $user->setEmail('sherlock@example.com');
        $user->setSetting(new UserSetting());
        $user->addRole(Roles::ROLE_USER);

        $manager->persist($user);
        $manager->flush();
    }
}
