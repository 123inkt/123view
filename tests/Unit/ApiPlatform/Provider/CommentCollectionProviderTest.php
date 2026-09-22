<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\ApiPlatform\Provider\CommentCollectionProvider;
use DR\Review\Entity\Review\Comment;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[CoversClass(CommentCollectionProvider::class)]
class CommentCollectionProviderTest extends AbstractTestCase
{
    /** @var ProviderInterface<Comment>&MockObject */
    private ProviderInterface&MockObject $collectionProvider;
    private CommentOutputFactory&MockObject $outputFactory;
    private CommentCollectionProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->outputFactory      = $this->createMock(CommentOutputFactory::class);
        $this->provider           = new CommentCollectionProvider($this->collectionProvider, $this->outputFactory);
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
        $operation     = new GetCollection();
        $uriVariables  = ['review.id' => '123'];
        $context       = ['filters' => ['state' => 'open']];
        $comment       = new Comment();
        $output        = static::createStub(CommentOutput::class);

        $this->collectionProvider
            ->expects($this->once())
            ->method('provide')
            ->with($operation, $uriVariables, $context)
            ->willReturn(new ArrayIterator([$comment]));
        $this->outputFactory->expects($this->once())->method('create')->with($comment)->willReturn($output);

        $result = $this->provider->provide($operation, $uriVariables, $context);

        static::assertIsIterable($result);
        static::assertSame([$output], iterator_to_array($result));
    }

    public function testMapsPaginatorAndMetadata(): void
    {
        $operation    = new GetCollection();
        $uriVariables = ['review.id' => '123'];
        $context      = ['filters' => ['state' => 'open']];
        $comment      = new Comment();
        $output       = static::createStub(CommentOutput::class);
        $paginator     = new TraversablePaginator(new ArrayIterator([$comment]), 2, 10, 25);

        $this->collectionProvider->expects($this->once())->method('provide')->with($operation, $uriVariables, $context)->willReturn($paginator);
        $this->outputFactory->expects($this->once())->method('create')->with($comment)->willReturn($output);

        $result = $this->provider->provide($operation, $uriVariables, $context);

        static::assertInstanceOf(PaginatorInterface::class, $result);
        static::assertSame(2.0, $result->getCurrentPage());
        static::assertSame(10.0, $result->getItemsPerPage());
        static::assertSame(25.0, $result->getTotalItems());
        static::assertSame([$output], iterator_to_array($result));
    }

    public function testRejectsSingleResult(): void
    {
        $operation = new GetCollection();
        $comment  = new Comment();

        $this->collectionProvider->expects($this->once())->method('provide')->with($operation, [], [])->willReturn($comment);
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collection provider must return an iterable.');
        $this->provider->provide($operation);
    }
}
