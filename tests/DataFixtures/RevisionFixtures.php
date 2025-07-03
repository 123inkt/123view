<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;

class RevisionFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE_A = 'revision_a';
    public const string REFERENCE_B = 'revision_b';

    public const COMMIT_HASH_A = '4be1e7812887f74a872aee68d62c86d84cbf9f9e';
    public const COMMIT_HASH_B = 'd3482ab30e71616c5d85e4a7b58e53f3414b45c7';

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $repository = $this->getReference(RepositoryFixtures::REFERENCE, Repository::class);

        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setTitle('title');
        $revision->setDescription('description');
        $revision->setAuthorName('Sherlock Holmes');
        $revision->setAuthorEmail('sherlock@example.com');
        $revision->setCommitHash(self::COMMIT_HASH_A);
        $revision->setCreateTimestamp(12345678);
        $revision->setFirstBranch('first-branch');
        $manager->persist($revision);
        $this->addReference(self::REFERENCE_A, $revision);

        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setTitle('book');
        $revision->setDescription('detective');
        $revision->setAuthorName('John Watson');
        $revision->setAuthorEmail('watson@example.com');
        $revision->setCommitHash(self::COMMIT_HASH_B);
        $revision->setCreateTimestamp(12345679);
        $manager->persist($revision);
        $this->addReference(self::REFERENCE_B, $revision);

        $manager->flush();
    }
}
