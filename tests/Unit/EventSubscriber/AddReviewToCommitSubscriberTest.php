<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Event\CommitEvent;
use DR\Review\EventSubscriber\AddReviewToCommitSubscriber;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AddReviewToCommitSubscriber::class)]
class AddReviewToCommitSubscriberTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private AddReviewToCommitSubscriber     $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->subscriber       = new AddReviewToCommitSubscriber($this->reviewRepository);
    }

    public function testGetSubscribedEvents(): void
    {
        $expected = [CommitEvent::class => ['onCommitEvent']];
        $result   = AddReviewToCommitSubscriber::getSubscribedEvents();
        static::assertSame($expected, $result);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testOnCommitEventShouldSkipReviewWithoutRepository(): void
    {
        $this->reviewRepository->expects(self::never())->method('findOneByCommitHash');
        $event = new CommitEvent($this->createCommit());

        $this->subscriber->onCommitEvent($event);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testOnCommitEvent(): void
    {
        $repository = new Repository();
        $repository->setId(5);

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        $event = new CommitEvent($commit);

        $this->reviewRepository->expects($this->once())->method('findOneByCommitHash')->with(5, 'commit-hash');

        $this->subscriber->onCommitEvent($event);
    }
}
