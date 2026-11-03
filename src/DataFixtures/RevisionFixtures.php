<?php
declare(strict_types=1);

namespace DR\Review\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;

class RevisionFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE = 'pull-request-1680-revision';

    public function load(ObjectManager $manager): void
    {
        $repository = $this->getReference(RepositoryFixtures::REFERENCE, Repository::class);
        $review     = $this->getReference(CodeReviewFixtures::REFERENCE, CodeReview::class);

        $revision = new Revision();
        $revision->setRepository($repository);
        $revision->setReview($review);
        $revision->setCommitHash('1dacdfce730d1485bc1788407784bb48908113ec');
        $revision->setParentHash('c930e716fec12b0c5b2e75b89a113c0f618e1d0b');
        $revision->setTitle('Update dependency phpstan/phpstan-strict-rules to v2.1.1');
        $revision->setDescription('');
        $revision->setFirstBranch('renovate/phpstan-packages');
        $revision->setAuthorName('renovate[bot]');
        $revision->setAuthorEmail('29139614+renovate[bot]@users.noreply.github.com');
        $revision->setCreateTimestamp(1793620404);

        $manager->persist($revision);
        $manager->flush();

        $this->addReference(self::REFERENCE, $revision);
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class, CodeReviewFixtures::class];
    }
}
