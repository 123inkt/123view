<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\Dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const string DEVELOPER_REFERENCE = 'development-user';
    public const string RENOVATE_REFERENCE  = 'renovate-user';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $developer = new User();
        $developer->setName('Developer');
        $developer->setEmail('development@example.com');
        $developer->setPassword($this->passwordHasher->hashPassword($developer, 'development'));
        $developer->addRole(Roles::ROLE_USER);
        $developer->getSetting();
        $developer->getReviewSetting();

        $renovate = new User();
        $renovate->setName('Renovate Bot');
        $renovate->setEmail('29139614+renovate[bot]@users.noreply.github.com');

        $manager->persist($developer);
        $manager->persist($renovate);
        $manager->flush();

        $this->addReference(self::DEVELOPER_REFERENCE, $developer);
        $this->addReference(self::RENOVATE_REFERENCE, $renovate);
    }
}
