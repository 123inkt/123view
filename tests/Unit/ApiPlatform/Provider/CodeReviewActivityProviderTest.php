<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Factory\CodeReviewActivityOutputFactory;
use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewActivityProvider;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewActivityProvider::class)]
class CodeReviewActivityProviderTest extends AbstractTestCase
{
    /** @var MockObject&ProviderInterface<CodeReviewActivity> */
    private ProviderInterface&MockObject               $collectionProvider;
    private CodeReviewActivityOutputFactory&MockObject $outputFactory;
    private CodeReviewActivityProvider                 $activityProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->outputFactory      = $this->createMock(CodeReviewActivityOutputFactory::class);
        $this->activityProvider   = new CodeReviewActivityProvider($this->collectionProvider, $this->outputFactory);
    }

    public function testProvideShouldOnlySupportGetCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->activityProvider->provide(new Get());
    }

    public function testProvide(): void
    {
        $operation = new GetCollection();
        $activity  = new CodeReviewActivity();

        $output = $this->createMock(CodeReviewActivityOutput::class);

        $this->collectionProvider->expects($this->once())->method('provide')->with($operation)->willReturn(new ArrayIterator([$activity]));
        $this->outputFactory->expects($this->once())->method('create')->with($activity)->willReturn($output);

        $result = $this->activityProvider->provide(new GetCollection());
        static::assertSame([$output], $result);
    }
}
