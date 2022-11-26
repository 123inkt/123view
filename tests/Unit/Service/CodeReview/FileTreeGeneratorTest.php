<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\FileTreeGenerator
 */
class FileTreeGeneratorTest extends AbstractTestCase
{
    private FileTreeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new FileTreeGenerator();
    }

    /**
     * @covers ::generate
     */
    public function testGenerateEqualFilePath(): void
    {
        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = '/foo/bar/example.txt';

        $diffFileB                = new DiffFile();
        $diffFileB->filePathAfter = '/foo/bar/example.json';

        $node = $this->generator->generate([$diffFileA, $diffFileB]);

        $expected = new DirectoryTreeNode(
            'root',
            [
                new DirectoryTreeNode(
                    'foo',
                    [new DirectoryTreeNode('bar', [], [$diffFileA, $diffFileB])]
                )
            ]
        );

        static::assertEquals($expected, $node);
    }

    /**
     * @covers ::generate
     */
    public function testGenerateUnevenFilePath(): void
    {
        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = '/foo/example.txt';

        $diffFileB                = new DiffFile();
        $diffFileB->filePathAfter = '/foo/bar/example.json';

        $node = $this->generator->generate([$diffFileA, $diffFileB]);

        $expected = new DirectoryTreeNode(
            'root',
            [
                new DirectoryTreeNode(
                    'foo',
                    [new DirectoryTreeNode('bar', [], [$diffFileB])],
                    [$diffFileA]
                )
            ]
        );

        static::assertEquals($expected, $node);
    }
}
