<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\FileTreeGenerator
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
