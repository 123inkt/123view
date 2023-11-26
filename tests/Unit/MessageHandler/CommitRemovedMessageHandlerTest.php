<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\MessageHandler\CommitRemovedMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\Webhook\ReviewEventService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommitRemovedMessageHandler::class)]
class CommitRemovedMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject         $repositoryRepository;
    private RevisionRepository&MockObject           $revisionRepository;
    private RevisionVisibilityRepository&MockObject $visibilityRepository;
    private CodeReviewRepository&MockObject         $reviewRepository;
    private ReviewEventService&MockObject           $eventService;
    private CommitRemovedMessageHandler             $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->visibilityRepository = $this->createMock(RevisionVisibilityRepository::class);
        $this->reviewRepository     = $this->createMock(CodeReviewRepository::class);
        $this->eventService         = $this->createMock(ReviewEventService::class);
        $this->messageHandler       = new CommitRemovedMessageHandler(
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->visibilityRepository,
            $this->reviewRepository,
            $this->eventService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeAbsentRevision(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->revisionRepository->expects(self::once())->method('findOneBy')->with(['commitHash' => 'hash', 'repository' => 123])->willReturn(null);
        $this->revisionRepository->expects(self::never())->method('remove');

        ($this->messageHandler)(new CommitRemovedMessage(123, 'hash'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithReview(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $review = new CodeReview();
        $review->setId(789);
        $visibility = new RevisionVisibility();
        $revision   = new Revision();
        $revision->setId(456);
        $revision->setTitle('title');
        $revision->setReview($review);
        $review->getRevisions()->add($revision);

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->revisionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['commitHash' => 'hash', 'repository' => 123])
            ->willReturn($revision);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->eventService->expects(self::once())->method('revisionRemovedFromReview')->with($review, $revision, CodeReviewStateType::OPEN);
        $this->visibilityRepository->expects(self::once())->method('findBy')->willReturn([$visibility]);
        $this->visibilityRepository->expects(self::once())->method('removeAll')->with([$visibility]);
        $this->revisionRepository->expects(self::once())->method('remove')->with($revision, true);

        ($this->messageHandler)(new CommitRemovedMessage(123, 'hash'));

        static::assertCount(0, $review->getRevisions());
        static::assertNull($revision->getReview());
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithoutReview(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setId(456);

        $this->repositoryRepository->expects(self::once())->method('find')->with(123)->willReturn($repository);
        $this->revisionRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['commitHash' => 'hash', 'repository' => 123])
            ->willReturn($revision);
        $this->reviewRepository->expects(self::never())->method('save');
        $this->eventService->expects(self::never())->method('revisionRemovedFromReview');
        $this->visibilityRepository->expects(self::once())->method('findBy')->willReturn([]);
        $this->visibilityRepository->expects(self::once())->method('removeAll')->with([]);
        $this->revisionRepository->expects(self::once())->method('remove')->with($revision, true);

        ($this->messageHandler)(new CommitRemovedMessage(123, 'hash'));
    }
}
