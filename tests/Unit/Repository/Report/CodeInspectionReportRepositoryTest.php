<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Report;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeInspectionReportFixtures;
use DR\Review\Utility\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionReportRepository::class)]
class CodeInspectionReportRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testFindByRevisions(): void
    {
        $repository = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        $revision = new Revision();
        $revision->setCommitHash('commit-hash');

        $reports = self::getService(CodeInspectionReportRepository::class)->findByRevisions($repository, [$revision]);
        static::assertCount(1, $reports);
    }

    /**
     * @throws Exception
     */
    public function testCleanUp(): void
    {
        $reportRepository = self::getService(CodeInspectionReportRepository::class);

        static::assertSame(0, $reportRepository->cleanUp(123456788));
        static::assertSame(1, $reportRepository->cleanUp(123456790));
    }

    protected function getFixtures(): array
    {
        return [CodeInspectionReportFixtures::class];
    }
}
