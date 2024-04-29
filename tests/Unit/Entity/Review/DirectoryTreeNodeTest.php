<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Review;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Tests\AbstractTestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(DirectoryTreeNode::class)]
class DirectoryTreeNodeTest extends AbstractTestCase
{
    public function testGetDirectory(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        $nodeB = new DirectoryTreeNode('nodeB');

        $node = new DirectoryTreeNode('foo', null, [$nodeA, $nodeB]);
        static::assertSame($nodeA, $node->getDirectory('nodeA'));
        static::assertNull($node->getDirectory('foobar'));
    }

    public function testFlattenEqualPath(): void
    {
        $node = new DirectoryTreeNode('');
        $node->addNode(['foo', 'bar', 'example.txt'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.json'], new stdClass());

        // flatten
        $node->flatten();

        $expected = new DirectoryTreeNode('/foo/bar', null, [], [new stdClass(), new stdClass()]);
        static::assertEquals($expected, $node);
    }

    public function testFlattenUnevenPath(): void
    {
        $node = new DirectoryTreeNode('');
        $node->addNode(['foo', 'example.txt'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.json'], new stdClass());

        // flatten
        $node->flatten();

        $expected = new DirectoryTreeNode(
            '/foo',
            null,
            [new DirectoryTreeNode('bar', null, [], [new stdClass()])],
            [new stdClass()]
        );
        static::assertEquals($expected, $node);
    }

    public function testFlattenEvenSubdirectoryPath(): void
    {
        $node = new DirectoryTreeNode('');
        $node->addNode(['src', 'example.txt'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.json'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.txt'], new stdClass());

        // flatten
        $node->flatten();

        $expected = new DirectoryTreeNode(
            '',
            null,
            [
                new DirectoryTreeNode('src', null, [], [new stdClass()]),
                new DirectoryTreeNode('foo/bar', null, [], [new stdClass(), new stdClass()])
            ],
        );
        static::assertEquals($expected, $node);
    }

    public function testSort(): void
    {
        $objA           = new stdClass();
        $objA->fileName = 'example.txt';
        $objB           = new stdClass();
        $objB->fileName = 'example.json';

        $node = new DirectoryTreeNode('');
        $node->addNode(['foo', 'example.txt'], $objA);
        $node->addNode(['foo', 'example.json'], $objB);

        $node->sort(static fn(stdClass $left, stdClass $right) => strcmp($left->fileName, $right->fileName));

        $expected = new DirectoryTreeNode('');
        $expected->addNode(['foo', 'example.json'], $objB);
        $expected->addNode(['foo', 'example.txt'], $objA);

        static::assertEquals($expected, $node);
    }

    public function testAddNodeEmptyPathIsDisallowed(): void
    {
        $this->expectException(LogicException::class);
        (new DirectoryTreeNode(''))->addNode([], new stdClass());
    }

    public function testGetDirectories(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        $nodeB = new DirectoryTreeNode('nodeB');

        $node = new DirectoryTreeNode('foo', null, [$nodeA, $nodeB]);
        static::assertSame([$nodeA, $nodeB], $node->getDirectories());
    }

    public function testGetName(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        static::assertSame('nodeA', $nodeA->getName());
    }

    public function testIsEmpty(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        static::assertTrue($nodeA->isEmpty());

        $nodeB = new DirectoryTreeNode('nodeB', null, [$nodeA]);
        static::assertFalse($nodeB->isEmpty());
    }

    public function testGetFiles(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        $node = new DirectoryTreeNode('foo', null, [], [$objA, $objB]);
        static::assertSame([$objA, $objB], $node->getFiles());
    }

    public function testGetFilesRecursive(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        $node = new DirectoryTreeNode('root', null, [], []);
        $node->addNode(['one'], $objA);
        $node->addNode(['one', 'two'], $objB);
        static::assertSame([$objA, $objB], $node->getFilesRecursive());
    }

    public function testGetFirstFileInTree(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        /** @var DirectoryTreeNode<stdClass> $node */
        $node = new DirectoryTreeNode('');
        static::assertNull($node->getFirstFileInTree());

        $node->addNode(['foo', 'example.json'], $objA);
        $node->addNode(['foo', 'bar', 'example.txt'], $objB);
        $node->flatten();

        static::assertSame($objB, $node->getFirstFileInTree());
    }

    /**
     * When there are no files in the tree, should return null
     */
    public function testGetFirstFileInTreeEmptyDirectories(): void
    {
        /** @var DirectoryTreeNode<stdClass> $node */
        $node = new DirectoryTreeNode('foo', null, [new DirectoryTreeNode('bar')]);
        static::assertNull($node->getFirstFileInTree());
    }

    public function testGetFileIterator(): void
    {
        $fileA = new DiffFile();
        $fileB = new DiffFile();

        $node = new DirectoryTreeNode('foo');
        $node->addNode(['path', 'to', 'file.txt'], $fileA);
        $node->addNode(['path', 'to', 'second', 'file.txt'], $fileB);

        static::assertSame([$fileB, $fileA], iterator_to_array($node->getFileIterator()));
    }

    public function testGetPathname(): void
    {
        $node = new DirectoryTreeNode('root');
        $node->addNode(['path', 'to', 'file.txt'], new stdClass());

        static::assertSame('path', $node->getDirectories()[0]->getPathname());
    }
}
