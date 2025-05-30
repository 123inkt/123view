<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Service\Webhook\WebhookExecutionService;
use DR\Review\Service\Webhook\WebhookNotifier;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(WebhookNotifier::class)]
class WebhookNotifierTest extends AbstractTestCase
{
    private WebhookRepository&MockObject       $webhookRepository;
    private WebhookExecutionService&MockObject $executionService;
    private CodeReviewRepository&MockObject    $reviewRepository;
    private WebhookNotifier                    $notifier;

    public function setUp(): void
    {
        parent::setUp();
        $this->webhookRepository = $this->createMock(WebhookRepository::class);
        $this->executionService  = $this->createMock(WebhookExecutionService::class);
        $this->reviewRepository  = $this->createMock(CodeReviewRepository::class);
        $this->notifier          = new WebhookNotifier($this->webhookRepository, $this->executionService, $this->reviewRepository);
    }

    public function testNotifyUnknownReview(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->webhookRepository->expects(self::never())->method('findByRepositoryId');

        $event = $this->createMock(CodeReviewAwareInterface::class);
        $event->method('getReviewId')->willReturn(123);

        $this->notifier->notify($event);
    }

    public function testNotify(): void
    {
        $repository = new Repository();
        $repository->setId(456);

        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);

        $webhook = new Webhook();
        $webhook->setId(789);

        $event = $this->createMock(CodeReviewAwareInterface::class);
        $event->method('getReviewId')->willReturn(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->webhookRepository->expects($this->once())->method('findByRepositoryId')->with(456, true)->willReturn([$webhook]);
        $this->executionService->expects($this->once())->method('execute')->with($webhook, $event);

        $this->notifier->notify($event);
    }
}
