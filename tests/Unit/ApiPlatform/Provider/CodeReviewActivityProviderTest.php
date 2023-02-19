<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewActivityProvider;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\Provider\CodeReviewActivityProvider
 * @covers ::__construct
 */
class CodeReviewActivityProviderTest extends AbstractTestCase
{
    /** @var MockObject&ProviderInterface<CodeReviewActivity> */
    private ProviderInterface&MockObject $collectionProvider;
    private CodeReviewActivityProvider   $activityProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->activityProvider   = new CodeReviewActivityProvider($this->collectionProvider);
    }

    /**
     * @covers ::provide
     */
    public function testProvideShouldOnlySupportGetCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->activityProvider->provide(new Get());
    }

    /**
     * @covers ::provide
     */
    public function testProvide(): void
    {
        $operation = new GetCollection();

        $user = new User();
        $user->setId(123);
        $review = new CodeReview();
        $review->setId(456);
        $activity = new CodeReviewActivity();
        $activity->setId(789);
        $activity->setEventName('event');
        $activity->setReview($review);
        $activity->setUser($user);
        $activity->setData(['data' => 'data']);
        $activity->setCreateTimestamp(135);

        $this->collectionProvider->expects(self::once())->method('provide')->with($operation)->willReturn(new ArrayIterator([$activity]));

        $result = $this->activityProvider->provide(new GetCollection());
        static::assertCount(1, $result);

        $expected = new CodeReviewActivityOutput(789, 123, 456, 'event', ['data' => 'data'], 135);
        static::assertEquals($expected, $result[0]);
    }
}
