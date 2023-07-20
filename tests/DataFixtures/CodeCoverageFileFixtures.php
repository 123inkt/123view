<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\CodeCoverageReport;
use DR\Review\Entity\Report\LineCoverage;
use DR\Utils\Assert;

class CodeCoverageFileFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $report = Assert::notNull($manager->getRepository(CodeCoverageReport::class)->findOneBy(['commitHash' => 'commit-hash']));

        $issue = new CodeCoverageFile();
        $issue->setFile('filepath');
        $issue->setCoverage((new LineCoverage())->setCoverage(123, 1)->setCoverage(456, 0));
        $issue->setReport($report);

        $manager->persist($issue);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [CodeCoverageReportFixtures::class];
    }
}
