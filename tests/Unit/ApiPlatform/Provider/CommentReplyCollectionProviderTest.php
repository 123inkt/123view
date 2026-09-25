<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\ApiPlatform\Provider\CommentReplyCollectionProvider;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[CoversClass(CommentReplyCollectionProvider::class)]
class CommentReplyCollectionProviderTest extends AbstractTestCase
{
    /** @var ProviderInterface<CommentReply>&MockObject */
    private ProviderInterface&MockObject $collectionProvider;
    private CommentReplyOutputFactory&MockObject $outputFactory;
    private CommentReplyCollectionProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->outputFactory      = $this->createMock(CommentReplyOutputFactory::class);
        $this->provider           = new CommentReplyCollectionProvider($this->collectionProvider, $this->outputFactory);
    }

    public function testOnlyCollectionOperationsAreAccepted(): void
    {
        $this->collectionProvider->expects($this->never())->method('provide');
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->provider->provide(new Get());
    }

    public function testMapsIterableWithoutSecondCall(): void
    {
        $operation    = new GetCollection();
        $uriVariables = ['comment.id' => '123'];
        $context      = ['filters' => ['comment.id' => '123']];
        $reply        = new CommentReply();
        $output       = static::createStub(CommentReplyOutput::class);

        $this->collectionProvider
            ->expects($this->once())
            ->method('provide')
            ->with($operation, $uriVariables, $context)
            ->willReturn(new ArrayIterator([$reply]));
        $this->outputFactory->expects($this->once())->method('create')->with($reply)->willReturn($output);

        $result = $this->provider->provide($operation, $uriVariables, $context);

        static::assertIsIterable($result);
        static::assertSame([$output], iterator_to_array($result));
    }

    public function testMapsPaginatorAndMetadata(): void
    {
        $operation = new GetCollection();
        $reply     = new CommentReply();
        $output    = static::createStub(CommentReplyOutput::class);
        $paginator  = new TraversablePaginator(new ArrayIterator([$reply]), 2, 10, 25);

        $this->collectionProvider->expects($this->once())->method('provide')->with($operation, [], [])->willReturn($paginator);
        $this->outputFactory->expects($this->once())->method('create')->with($reply)->willReturn($output);

        $result = $this->provider->provide($operation);

        static::assertInstanceOf(PaginatorInterface::class, $result);
        static::assertSame(2.0, $result->getCurrentPage());
        static::assertSame(10.0, $result->getItemsPerPage());
        static::assertSame(25.0, $result->getTotalItems());
        static::assertSame([$output], iterator_to_array($result));
    }

    public function testRejectsNonIterableCollectionResult(): void
    {
        $operation = new GetCollection();
        $reply     = new CommentReply();

        $this->collectionProvider->expects($this->once())->method('provide')->with($operation, [], [])->willReturn($reply);
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection provider must return an iterable.');
        $this->provider->provide($operation);
    }
}
