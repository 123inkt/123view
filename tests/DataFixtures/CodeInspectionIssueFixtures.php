<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Utility\Assert;

class CodeInspectionIssueFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $report = Assert::notNull($manager->getRepository(CodeInspectionReport::class)->findOneBy(['inspectionId' => 'inspectionId']));

        $issue = new CodeInspectionIssue();
        $issue->setFile('filepath');
        $issue->setLineNumber(123);
        $issue->setSeverity('error');
        $issue->setMessage('message');
        $issue->setRule('rule');
        $issue->setReport($report);

        $manager->persist($issue);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [CodeInspectionReportFixtures::class];
    }
}
