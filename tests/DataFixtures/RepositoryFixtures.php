<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;

class RepositoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $repository = new Repository();
        $repository->setActive(true);
        $repository->setFavorite(true);
        $repository->setName('repository');
        $repository->setDisplayName('displayName');
        $repository->setUrl('url');
        $repository->setCreateTimestamp(12345678);
        $repository->setUpdateRevisionsInterval(500);
        $repository->setUpdateRevisionsTimestamp(23456789);

        $manager->persist($repository);
        $manager->flush();
    }
}
