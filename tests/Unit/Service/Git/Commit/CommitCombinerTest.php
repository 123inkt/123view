<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Commit;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Git\Commit\CommitCombiner;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Commit\CommitCombiner
 */
class CommitCombinerTest extends AbstractTestCase
{
    private CommitCombiner $combiner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->combiner = new CommitCombiner();
    }

    /**
     * @covers ::combine
     */
    public function testCombineShouldThrowExceptionOnEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->combiner->combine([]);
    }

    /**
     * @covers ::combine
     */
    public function testCombineShouldReturnImmediatelyOnSizeOneCommits(): void
    {
        $commit = $this->createCommit();
        $result = $this->combiner->combine([$commit]);
        static::assertSame($commit, $result);
    }

    /**
     * @covers ::combine
     * @covers ::merge
     */
    public function testCombineShouldCombineHashesAndFiles(): void
    {
        $commitA               = $this->createCommit();
        $commitA->commitHashes = ['commitA'];
        $commitA->files        = [new DiffFile()];
        $commitB               = $this->createCommit();
        $commitB->commitHashes = ['commitB'];
        $commitB->files        = [new DiffFile()];

        $expectedHashes = ['commitA', 'commitB'];
        $expectedFiles  = array_merge($commitA->files, $commitB->files);

        $result = $this->combiner->combine([$commitA, $commitB]);
        static::assertSame($expectedHashes, $result->commitHashes);
        static::assertSame($expectedFiles, $result->files);
    }
}
