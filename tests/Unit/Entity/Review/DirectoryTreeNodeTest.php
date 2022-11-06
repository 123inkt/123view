<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Review;

use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use LogicException;
use stdClass;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Model\Review\DirectoryTreeNode
 * @covers ::__construct
 */
class DirectoryTreeNodeTest extends AbstractTestCase
{
    /**
     * @covers ::getDirectory
     */
    public function testGetDirectory(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        $nodeB = new DirectoryTreeNode('nodeB');

        $node = new DirectoryTreeNode('foo', [$nodeA, $nodeB]);
        static::assertSame($nodeA, $node->getDirectory('nodeA'));
        static::assertNull($node->getDirectory('foobar'));
    }

    /**
     * @covers ::flatten
     * @covers ::addNode
     */
    public function testFlattenEqualPath(): void
    {
        $node = new DirectoryTreeNode('');
        $node->addNode(['foo', 'bar', 'example.txt'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.json'], new stdClass());

        // flatten
        $node->flatten();

        $expected = new DirectoryTreeNode('/foo/bar', [], [new stdClass(), new stdClass()]);
        static::assertEquals($expected, $node);
    }

    /**
     * @covers ::flatten
     * @covers ::addNode
     */
    public function testFlattenUnevenPath(): void
    {
        $node = new DirectoryTreeNode('');
        $node->addNode(['foo', 'example.txt'], new stdClass());
        $node->addNode(['foo', 'bar', 'example.json'], new stdClass());

        // flatten
        $node->flatten();

        $expected = new DirectoryTreeNode(
            '/foo',
            [new DirectoryTreeNode('bar', [], [new stdClass()])],
            [new stdClass()]
        );
        static::assertEquals($expected, $node);
    }

    /**
     * @covers ::flatten
     * @covers ::addNode
     */
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
            [
                new DirectoryTreeNode('src', [], [new stdClass()]),
                new DirectoryTreeNode('foo/bar', [], [new stdClass(), new stdClass()])
            ],
        );
        static::assertEquals($expected, $node);
    }

    /**
     * @covers ::addNode
     */
    public function addNodeEmptyPathIsDisallowed(): void
    {
        $this->expectException(LogicException::class);
        (new DirectoryTreeNode(''))->addNode([], new stdClass());
    }

    /**
     * @covers ::getDirectories
     */
    public function testGetDirectories(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        $nodeB = new DirectoryTreeNode('nodeB');

        $node = new DirectoryTreeNode('foo', [$nodeA, $nodeB]);
        static::assertSame([$nodeA, $nodeB], $node->getDirectories());
    }

    /**
     * @covers ::getName
     */
    public function testGetName(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        static::assertSame('nodeA', $nodeA->getName());
    }

    /**
     * @covers ::getName
     */
    public function testIsEmpty(): void
    {
        $nodeA = new DirectoryTreeNode('nodeA');
        static::assertTrue($nodeA->isEmpty());

        $nodeB = new DirectoryTreeNode('nodeB', [$nodeA]);
        static::assertFalse($nodeB->isEmpty());
    }

    /**
     * @covers ::getFiles
     */
    public function testGetFiles(): void
    {
        $objA = new stdClass();
        $objB = new stdClass();

        $node = new DirectoryTreeNode('foo', [], [$objA, $objB]);
        static::assertSame([$objA, $objB], $node->getFiles());
    }
}
