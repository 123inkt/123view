<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures\dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Repository\Repository;
use League\Uri\Uri;

class RepositoryFixtures extends Fixture
{
    public const string REFERENCE = '123view-repository';

    public function load(ObjectManager $manager): void
    {
        $repository = new Repository();
        $repository->setActive(true);
        $repository->setFavorite(true);
        $repository->setName('123view');
        $repository->setDisplayName('123view');
        $repository->setMainBranchName('master');
        $repository->setUrl(Uri::new('https://github.com/123inkt/123view.git'));
        $repository->setGitType(RepositoryGitType::GITHUB);
        $repository->setCreateTimestamp(time());
        $repository->setUpdateRevisionsTimestamp(time());

        $manager->persist($repository);
        $manager->flush();

        $this->addReference(self::REFERENCE, $repository);
    }
}
