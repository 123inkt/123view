<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Webhook;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Repository\Webhook\WebhookRepository;
use DR\GitCommitNotification\Service\Webhook\WebhookExecutionService;
use DR\GitCommitNotification\Service\Webhook\WebhookNotifier;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Webhook\WebhookNotifier
 * @covers ::__construct
 */
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

    /**
     * @covers ::notify
     */
    public function testNotifyUnknownReview(): void
    {
        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->webhookRepository->expects(self::never())->method('findBy');

        $event = $this->createMock(WebhookEventInterface::class);
        $event->method('getReviewId')->willReturn(123);

        $this->notifier->notify($event);
    }

    /**
     * @covers ::notify
     */
    public function testNotify(): void
    {
        $repository = new Repository();
        $repository->setId(456);

        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);

        $webhook = new Webhook();
        $webhook->setId(789);

        $event = $this->createMock(WebhookEventInterface::class);
        $event->method('getReviewId')->willReturn(123);

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->webhookRepository->expects(self::once())->method('findBy')->with(['enabled' => 1, 'repository' => 456])->willReturn([$webhook]);
        $this->executionService->expects(self::once())->method('execute')->with($webhook, $event);

        $this->notifier->notify($event);
    }
}
