<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Service\Mercure\MessagePublisher;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CodeReviewActivityPublisher::class)]
class CodeReviewActivityPublisherTest extends AbstractTestCase
{
    private CodeReviewActivityFormatter&MockObject    $formatter;
    private UserRepository&MockObject                 $userRepository;
    private CodeReviewActivityUrlGenerator&MockObject $urlGenerator;
    private MessagePublisher&MockObject               $publisher;
    private CodeReviewActivityPublisher               $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->formatter      = $this->createMock(CodeReviewActivityFormatter::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->urlGenerator   = $this->createMock(CodeReviewActivityUrlGenerator::class);
        $this->publisher      = $this->createMock(MessagePublisher::class);
        $this->service        = new CodeReviewActivityPublisher($this->formatter, $this->userRepository, $this->urlGenerator, $this->publisher);
    }

    /**
     * @throws Throwable
     */
    public function testPublishNoMessageNoPublish(): void
    {
        $activity = new CodeReviewActivity();

        $this->formatter->expects($this->once())->method('format')->with($activity)->willReturn(null);
        $this->publisher->expects($this->never())->method('publishToReview');
        $this->publisher->expects($this->never())->method('publishToUsers');
        $this->userRepository->expects($this->never())->method('findBy');
        $this->urlGenerator->expects($this->never())->method('generate');

        $this->service->publish($activity);
    }

    /**
     * @throws Throwable
     */
    public function testPublish(): void
    {
        $user  = (new User())->setId(123);
        $actor = (new User())->setId(987);
        $actor->getSetting()->setBrowserNotificationEvents(['event']);
        $repository = (new Repository())->setId(789)->setDisplayName('RP');
        $review     = (new CodeReview())->setId(456)->setProjectId(321)->setActors([567])->setTitle('title')->setRepository($repository);
        $activity   = new CodeReviewActivity();
        $activity->setId(135);
        $activity->setEventName('event');
        $activity->setUser($user);
        $activity->setReview($review);

        $reviewUpdate = new UpdateMessage(
            135,
            123,
            456,
            'event',
            'CR-321 - RP - title',
            'message',
            Http::new('url')
        );

        $this->formatter->expects($this->once())->method('format')->with($activity)->willReturn('message');
        $this->userRepository->expects($this->once())->method('findBy')->with(['id' => [567]])->willReturn([$actor]);
        $this->urlGenerator->expects($this->once())->method('generate')->with($activity)->willReturn('url');
        $this->publisher->expects($this->once())->method('publishToReview')->with($reviewUpdate);
        $this->publisher->expects($this->once())->method('publishToUsers')->with($reviewUpdate);

        $this->service->publish($activity);
    }
}
