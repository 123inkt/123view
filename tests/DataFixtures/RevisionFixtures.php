<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Utility\Assert;

class RevisionFixtures extends Fixture implements DependentFixtureInterface
{
    public const COMMIT_HASH = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setTitle('title');
        $revision->setDescription('description');
        $revision->setAuthorName('Sherlock Holmes');
        $revision->setAuthorEmail('sherlock@example.com');
        $revision->setCommitHash(self::COMMIT_HASH);
        $revision->setCreateTimestamp(12345678);

        $manager->persist($revision);
        $manager->flush();
    }
}
