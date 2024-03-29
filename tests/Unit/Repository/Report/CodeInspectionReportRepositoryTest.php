<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Report;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeInspectionReportFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionReportRepository::class)]
class CodeInspectionReportRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testFindBranchIds(): void
    {
        $repository = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        $revision = new Revision();
        $revision->setCommitHash('commit-hash');

        $branchIds = self::getService(CodeInspectionReportRepository::class)->findBranchIds($repository, [$revision]);
        static::assertSame([['inspectionId', 'branchId']], $branchIds);
    }

    /**
     * @throws Exception
     */
    public function testFindByRevisions(): void
    {
        $repository = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));

        $revision = new Revision();
        $revision->setCommitHash('commit-hash');

        $reports = self::getService(CodeInspectionReportRepository::class)->findByRevisions($repository, [$revision], [['inspectionId', 'branchId']]);
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

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeInspectionReportFixtures::class];
    }
}
