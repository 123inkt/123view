<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber\MarkDown;

use ArrayIterator;
use DR\Review\EventSubscriber\MarkDown\AbsoluteImageUrlSubscriber;
use DR\Review\Service\Markdown\DocumentNodeIteratorFactory;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AbsoluteImageUrlSubscriber::class)]
class AbsoluteImageUrlSubscriberTest extends AbstractTestCase
{
    private DocumentNodeIteratorFactory&MockObject $iteratorFactory;
    private AbsoluteImageUrlSubscriber             $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iteratorFactory = $this->createMock(DocumentNodeIteratorFactory::class);
        $this->subscriber      = new AbsoluteImageUrlSubscriber($this->iteratorFactory, 'https://123view.com/');
    }

    public function testHandleNonImageNodeShouldBeSkipped(): void
    {
        $document    = $this->createMock(Document::class);
        $defaultNode = $this->createMock(Node::class);
        $iterator    = new ArrayIterator([$defaultNode]);

        $this->iteratorFactory->expects($this->once())->method('iterate')->with($document)->willReturn($iterator);

        $this->subscriber->handle(new DocumentParsedEvent($document));
    }

    public function testHandleAbsoluteImageNodeShouldBeSkipped(): void
    {
        $document  = $this->createMock(Document::class);
        $imageNode = $this->createMock(Image::class);
        $iterator  = new ArrayIterator([$imageNode]);

        $imageNode->expects($this->once())->method('getUrl')->willReturn('https://123view.com/app/image/123.png');
        $imageNode->expects($this->never())->method('setUrl');
        $this->iteratorFactory->expects($this->once())->method('iterate')->with($document)->willReturn($iterator);

        $this->subscriber->handle(new DocumentParsedEvent($document));
    }

    public function testHandleImageUrlShouldBeAbsolute(): void
    {
        $document  = $this->createMock(Document::class);
        $imageNode = $this->createMock(Image::class);
        $iterator  = new ArrayIterator([$imageNode]);

        $imageNode->expects($this->exactly(2))->method('getUrl')->willReturn('/app/image/123.png');
        $imageNode->expects($this->once())->method('setUrl')->with('https://123view.com/app/image/123.png');
        $this->iteratorFactory->expects($this->once())->method('iterate')->with($document)->willReturn($iterator);

        $this->subscriber->handle(new DocumentParsedEvent($document));
    }
}
