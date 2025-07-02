<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Revision;

use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\RevisionFileFixtures;
use DR\Utils\Arrays;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RevisionFileRepository::class)]
class RevisionFileRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testGetFileChanges(): void
    {
        $revision = self::getService(RevisionRepository::class)->findOneBy(['title' => 'title']);
        static::assertNotNull($revision);

        $repository = self::getService(RevisionFileRepository::class);
        $changes = $repository->getFileChanges([$revision]);

        static::assertCount(1, $changes);

        $change = Arrays::first($changes);
        static::assertSame(2, $change->fileCount);
        static::assertSame(68, $change->linesAdded);
        static::assertSame(112, $change->linesRemoved);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RevisionFileFixtures::class];
    }
}
