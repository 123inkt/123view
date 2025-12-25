<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\MessageHandler\DiffFileCacheMessageHandler;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Service\Util\SystemLoadService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Throwable;

#[CoversClass(DiffFileCacheMessageHandler::class)]
class DiffFileCacheMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject       $reviewRepository;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private SystemLoadService&MockObject          $loadService;
    private DiffFileCacheMessageHandler           $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->diffService      = $this->createMock(ReviewDiffServiceInterface::class);
        $this->loadService      = $this->createMock(SystemLoadService::class);
        $this->messageHandler   = new DiffFileCacheMessageHandler($this->reviewRepository, $this->diffService, $this->loadService);
        $this->messageHandler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventMissingReview(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->diffService->expects($this->never())->method('getDiffForRevisions');

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventNoRevisions(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->never())->method('getDiffForRevisions');
        $this->loadService->expects($this->once())->method('getLoad')->willReturn(0.0);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventHighLoad(): void
    {
        $review = (new CodeReview())->setId(123);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->never())->method('getDiffForRevisions');
        $this->loadService->expects($this->once())->method('getLoad')->willReturn(1.2);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventBranchReview(): void
    {
        $review = new CodeReview();
        $review->setType(CodeReviewType::BRANCH);

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->never())->method('getDiffForRevisions');
        $this->loadService->expects($this->never())->method('getLoad');

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEvent(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $repository->setId(456);

        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $file                = new DiffFile();
        $file->filePathAfter = 'file-path-after';

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->loadService->expects($this->once())->method('getLoad')->willReturn(0.0);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventShouldContinueOnFailure(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $repository->setId(456);

        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $file                = new DiffFile();
        $file->filePathAfter = 'file-path-after';

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->loadService->expects($this->once())->method('getLoad')->willReturn(0.0);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @throws Throwable
     */
    public function testHandleEventShouldSkipHighlightingForDeletedFile(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $repository->setId(456);

        $review = new CodeReview();
        $review->setId(123);
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $file                 = new DiffFile();
        $file->filePathBefore = 'file-path-before';

        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);
        $this->loadService->expects($this->once())->method('getLoad')->willReturn(0.0);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }
}
