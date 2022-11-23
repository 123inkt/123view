<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DR\GitCommitNotification\Entity\Review\Revision;

class RevisionFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $revision = new Revision();
        $revision->setTitle('title');
        $revision->setDescription('description');
        $revision->setAuthorName('Sherlock Holmes');
        $revision->setAuthorEmail('sherlock@example.com');
        $revision->setCommitHash('abcdefghijklmnopqrstuvwxyz');
        $revision->setCreateTimestamp(12345678);

        $manager->persist($revision);
        $manager->flush();
    }
}
