<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;
use League\Uri\Uri;

class RepositoryFixtures extends Fixture
{
    public const string REFERENCE = 'repository';

    public function load(ObjectManager $manager): void
    {
        $repository = new Repository();
        $repository->setActive(true);
        $repository->setFavorite(true);
        $repository->setName('repository');
        $repository->setDisplayName('displayName');
        $repository->setUrl(Uri::new('url'));
        $repository->setCreateTimestamp(12345678);
        $repository->setUpdateRevisionsInterval(500);
        $repository->setUpdateRevisionsTimestamp(23456789);

        $manager->persist($repository);
        $manager->flush();

        $this->addReference(self::REFERENCE, $repository);
    }
}
