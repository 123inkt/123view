<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Factory\CodeReviewActivityOutputFactory;
use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Factory\UserOutputFactory;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewActivityOutputFactory::class)]
class CodeReviewActivityOutputFactoryTest extends AbstractTestCase
{
    private UserOutputFactory&MockObject       $userOutputFactory;
    private CodeReviewOutputFactory&MockObject $reviewOutputFactory;
    private CodeReviewActivityOutputFactory    $outputFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userOutputFactory   = $this->createMock(UserOutputFactory::class);
        $this->reviewOutputFactory = $this->createMock(CodeReviewOutputFactory::class);
        $this->outputFactory       = new CodeReviewActivityOutputFactory($this->userOutputFactory, $this->reviewOutputFactory);
    }

    public function testCreate(): void
    {
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

        $this->userOutputFactory->expects($this->once())->method('create')->with($user);
        $this->reviewOutputFactory->expects($this->once())->method('create')->with($review);

        $output = $this->outputFactory->create($activity);
        static::assertSame(789, $output->id);
        static::assertSame('event', $output->eventName);
        static::assertSame(['data' => 'data'], $output->data);
        static::assertSame(135, $output->createTimestamp);
    }
}
