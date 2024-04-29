<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeReview\FileTreeGenerator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileTreeGenerator::class)]
class FileTreeGeneratorTest extends AbstractTestCase
{
    private FileTreeGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new FileTreeGenerator();
    }

    public function testGenerateEqualFilePath(): void
    {
        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = '/foo/bar/example.txt';

        $diffFileB                = new DiffFile();
        $diffFileB->filePathAfter = '/foo/bar/example.json';

        $node = $this->generator->generate([$diffFileA, $diffFileB]);

        $expected = new DirectoryTreeNode(
            'root',
            null,
            [
                new DirectoryTreeNode(
                    'foo',
                    null,
                    [new DirectoryTreeNode('bar', null, [], [$diffFileA, $diffFileB])]
                )
            ]
        );

        static::assertEquals($expected, $node);
    }

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
