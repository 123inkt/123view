<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher;
use DR\Review\Tests\AbstractTestCase;
use Nette\Utils\Json;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher
 * @covers ::__construct
 */
class CodeReviewActivityPublisherTest extends AbstractTestCase
{
    private CodeReviewActivityFormatter&MockObject $formatter;
    private UserRepository&MockObject              $userRepository;
    private UrlGeneratorInterface&MockObject       $urlGenerator;
    private HubInterface&MockObject                $mercureHub;
    private CodeReviewActivityPublisher            $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->formatter      = $this->createMock(CodeReviewActivityFormatter::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->urlGenerator   = $this->createMock(UrlGeneratorInterface::class);
        $this->mercureHub     = $this->createMock(HubInterface::class);
        $this->service        = new CodeReviewActivityPublisher($this->formatter, $this->userRepository, $this->urlGenerator, $this->mercureHub);
    }

    /**
     * @covers ::publish
     * @throws Throwable
     */
    public function testPublishNoMessageNoPublish(): void
    {
        $activity = new CodeReviewActivity();

        $this->formatter->expects(self::once())->method('format')->with($activity)->willReturn(null);
        $this->mercureHub->expects(self::never())->method('publish');

        $this->service->publish($activity);
    }

    /**
     * @covers ::publish
     * @throws Throwable
     */
    public function testPublish(): void
    {
        $user  = (new User())->setId(123);
        $actor = (new User())->setId(987);
        $actor->getSetting()->setBrowserNotificationEvents(['event']);
        $repository = (new Repository())->setId(789)->setDisplayName('RP');
        $review     = (new CodeReview())->setId(456)->setProjectId(321)->setRepository($repository);
        $activity   = new CodeReviewActivity();
        $activity->setId(135);
        $activity->setEventName('event');
        $activity->setUser($user);
        $activity->setReview($review);

        $reviewUpdate = new Update(
            '/review/456',
            Json::encode(
                [
                    'topic'     => '/review/456',
                    'eventId'   => 135,
                    'userId'    => 123,
                    'reviewId'  => 456,
                    'eventName' => 'event',
                    'title'     => 'CR-321 - RP',
                    'message'   => 'message',
                    'url'       => 'url',
                ]
            ),
            true
        );

        $userUpdate = new Update(
            '/user/987',
            Json::encode(
                [
                    'topic'     => '/user/987',
                    'eventId'   => 135,
                    'userId'    => 123,
                    'reviewId'  => 456,
                    'eventName' => 'event',
                    'title'     => 'CR-321 - RP',
                    'message'   => 'message',
                    'url'       => 'url',
                ]
            ),
            true
        );

        $this->formatter->expects(self::once())->method('format')->with($activity)->willReturn('message');
        $this->userRepository->expects(self::once())->method('getActors')->with(456)->willReturn([$actor]);
        $this->urlGenerator->expects(self::once())->method('generate')->with(ReviewController::class, ['review' => $review])->willReturn('url');
        $this->mercureHub->expects(self::exactly(2))->method('publish')->withConsecutive([$reviewUpdate], [$userUpdate]);

        $this->service->publish($activity);
    }
}
