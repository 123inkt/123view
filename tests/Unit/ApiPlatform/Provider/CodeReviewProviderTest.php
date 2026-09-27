<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewProvider::class)]
class CodeReviewProviderTest extends AbstractTestCase
{
    /** @var MockObject&ProviderInterface<CodeReview> */
    private ProviderInterface&MockObject       $collectionProvider;
    /** @var MockObject&ProviderInterface<CodeReview> */
    private ProviderInterface&MockObject       $itemProvider;
    private CodeReviewOutputFactory&MockObject $reviewOutputFactory;
    private CodeReviewProvider                 $reviewProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider  = $this->createMock(ProviderInterface::class);
        $this->itemProvider        = $this->createMock(ProviderInterface::class);
        $this->reviewOutputFactory = $this->createMock(CodeReviewOutputFactory::class);
        $this->reviewProvider      = new CodeReviewProvider($this->collectionProvider, $this->itemProvider, $this->reviewOutputFactory);
    }

    public function testProvideShouldRejectUnsupported(): void
    {
        $this->collectionProvider->expects($this->never())->method('provide');
        $this->itemProvider->expects($this->never())->method('provide');
        $this->reviewOutputFactory->expects($this->never())->method('create');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection or Get operation is supported');
        $this->reviewProvider->provide(new Patch());
    }

    public function testProvide(): void
    {
        $operation = new GetCollection();
        $review    = new CodeReview();

        $output = static::createStub(CodeReviewOutput::class);

        // setup mocks
        $this->collectionProvider->expects($this->once())->method('provide')->with($operation)->willReturn(new ArrayIterator([$review]));
        $this->itemProvider->expects($this->never())->method('provide');
        $this->reviewOutputFactory->expects($this->once())->method('create')->with($review)->willReturn($output);

        // execute test
        $result = $this->reviewProvider->provide($operation);
        static::assertSame([$output], $result);
    }

    public function testProvideItem(): void
    {
        $operation    = new Get();
        $review       = new CodeReview();
        $output       = static::createStub(CodeReviewOutput::class);
        $uriVariables = ['id' => 123];
        $context      = ['key' => 'value'];

        $this->collectionProvider->expects($this->never())->method('provide');
        $this->itemProvider->expects($this->once())->method('provide')->with($operation, $uriVariables, $context)->willReturn($review);
        $this->reviewOutputFactory->expects($this->once())->method('create')->with($review)->willReturn($output);

        static::assertSame($output, $this->reviewProvider->provide($operation, $uriVariables, $context));
    }

    public function testProvideItemWithoutReview(): void
    {
        $operation = new Get();

        $this->collectionProvider->expects($this->never())->method('provide');
        $this->itemProvider->expects($this->once())->method('provide')->with($operation)->willReturn(null);
        $this->reviewOutputFactory->expects($this->never())->method('create');

        static::assertNull($this->reviewProvider->provide($operation));
    }
}
