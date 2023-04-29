<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Utility\Assert;

class CodeInspectionReportFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $report = new CodeInspectionReport();
        $report->setRepository($repository);
        $report->setInspectionId('inspectionId');
        $report->setCommitHash('commit-hash');
        $report->setCreateTimestamp(123456789);

        $manager->persist($report);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }
}
