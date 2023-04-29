<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Report;

use DR\Review\Repository\Report\CodeInspectionIssueRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeInspectionIssueFixtures;
use DR\Review\Utility\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionIssueRepository::class)]
class CodeInspectionIssueRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testFindByFile(): void
    {
        $issueRepository = self::getService(CodeInspectionIssueRepository::class);

        $report = Assert::notNull(self::getService(CodeInspectionReportRepository::class)->findOneBy(['inspectionId' => 'inspectionId']));

        $issues = $issueRepository->findByFile([$report], 'filepath');
        static::assertCount(1, $issues);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeInspectionIssueFixtures::class];
    }
}
