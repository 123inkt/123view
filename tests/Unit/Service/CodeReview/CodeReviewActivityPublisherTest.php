<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityPublisher;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityUrlGenerator;
use DR\Review\Service\Mercure\MessagePublisher;
use DR\Review\Tests\AbstractTestCase;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mercure\Update;
use Throwable;
use function DR\PHPUnitExtensions\Mock\consecutive;

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
        $this->publisher->expects(self::never())->method('publish');

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

        $reviewUpdate = new Update(
            '/review/456',
            Json::encode(
                [
                    'topic'     => '/review/456',
                    'eventId'   => 135,
                    'userId'    => 123,
                    'reviewId'  => 456,
                    'eventName' => 'event',
                    'title'     => 'CR-321 - RP - title',
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
                    'title'     => 'CR-321 - RP - title',
                    'message'   => 'message',
                    'url'       => 'url',
                ]
            ),
            true
        );

        $this->formatter->expects($this->once())->method('format')->with($activity)->willReturn('message');
        $this->userRepository->expects($this->once())->method('findBy')->with(['id' => [567]])->willReturn([$actor]);
        $this->urlGenerator->expects($this->once())->method('generate')->with($activity)->willReturn('url');
        $this->publisher->expects($this->exactly(2))
            ->method('publish')
            ->with(...consecutive([$reviewUpdate], [$userUpdate]))
            ->willReturn('success');

        $this->service->publish($activity);
    }
}
