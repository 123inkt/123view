<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Markdown;

use DR\Review\Service\Markdown\DocumentNodeIteratorFactory;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Node\Node;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocumentNodeIteratorFactory::class)]
class DocumentNodeIteratorFactoryTest extends AbstractTestCase
{
    private DocumentNodeIteratorFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new DocumentNodeIteratorFactory();
    }

    public function testIterate(): void
    {
        $nextNode = $this->createMock(Node::class);

        $childNode = $this->createMock(Node::class);
        $childNode->method('next')->willReturn($nextNode);

        $node = $this->createMock(Node::class);
        $node->method('firstChild')->willReturn($childNode);

        $nodes = [];
        foreach ($this->factory->iterate($node) as $iteratedNode) {
            $nodes[] = $iteratedNode;
        }

        static::assertSame([$node, $childNode, $nextNode], $nodes);
    }
}
