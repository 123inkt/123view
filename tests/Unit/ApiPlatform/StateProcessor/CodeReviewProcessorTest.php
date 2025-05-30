<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Patch;
use DR\Review\ApiPlatform\StateProcessor\CodeReviewProcessor;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[CoversClass(CodeReviewProcessor::class)]
class CodeReviewProcessorTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private ReviewEventService&MockObject   $eventService;
    private User&MockObject                 $user;
    private CodeReviewProcessor             $reviewProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->eventService     = $this->createMock(ReviewEventService::class);
        $this->user             = $this->createMock(User::class);
        $this->reviewProcessor  = new CodeReviewProcessor($this->reviewRepository, $this->eventService, $this->user);
    }

    public function testProcessShouldSkipNonReview(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Expecting value to be instance of ' . CodeReview::class);
        $this->reviewProcessor->process('foobar', new Patch()); // @phpstan-ignore-line
    }

    public function testProcessShouldNotEmitEvent(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        static::assertSame($review, $this->reviewProcessor->process($review, new Patch()));
    }

    public function testProcessShouldEmitEvent(): void
    {
        $review = new CodeReview();
        $review->setState(CodeReviewStateType::CLOSED);

        $this->user->expects($this->once())->method('getId')->willReturn(123);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);
        $this->eventService->expects($this->once())->method('reviewStateChanged')->with($review, 'open', 123);

        static::assertSame($review, $this->reviewProcessor->process($review, new Patch()));
    }
}
