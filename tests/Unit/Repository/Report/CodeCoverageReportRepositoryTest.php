<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Report;

use DR\Review\Repository\Report\CodeCoverageReportRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeCoverageFileFixtures;
use DR\Review\Tests\DataFixtures\CodeCoverageReportFixtures;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeCoverageReportRepository::class)]
class CodeCoverageReportRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testCleanUp(): void
    {
        $reportRepository = self::getService(CodeCoverageReportRepository::class);

        static::assertSame(0, $reportRepository->cleanUp(123456788));
        static::assertSame(1, $reportRepository->cleanUp(123456790));
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeCoverageReportFixtures::class, CodeCoverageFileFixtures::class];
    }
}
