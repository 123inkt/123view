<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;

class RevisionFileFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RevisionFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $revision = $this->getReference(RevisionFixtures::REFERENCE_A, Revision::class);

        $revisionFileA = new RevisionFile();
        $revisionFileA->setRevision($revision);
        $revisionFileA->setFilepath('/file/path/a');
        $revisionFileA->setLinesAdded(12);
        $revisionFileA->setLinesRemoved(34);

        $revisionFileB = new RevisionFile();
        $revisionFileB->setRevision($revision);
        $revisionFileB->setFilepath('/file/path/b');
        $revisionFileB->setLinesAdded(56);
        $revisionFileB->setLinesRemoved(78);

        $manager->persist($revisionFileA);
        $manager->persist($revisionFileB);
        $manager->flush();
    }
}
