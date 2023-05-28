<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Report;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeCoverageFileRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CodeCoverageFileFixtures;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Review\Utility\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeCoverageFileRepository::class)]
class CodeCoverageFileRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testFindOneByRevisions(): void
    {
        $filePath   = 'filepath';
        $repository = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $revision   = new Revision();
        $revision->setCommitHash('commit-hash');

        $fileRepository = self::getService(CodeCoverageFileRepository::class);

        // find nothing
        static::assertNull($fileRepository->findOneByRevisions($repository, [$revision], 'foobar'));

        // find file
        $fileCoverage = $fileRepository->findOneByRevisions($repository, [$revision], $filePath);
        static::assertNotNull($fileCoverage);
        static::assertSame($filePath, $fileCoverage->getFile());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RepositoryFixtures::class, CodeCoverageFileFixtures::class];
    }
}
