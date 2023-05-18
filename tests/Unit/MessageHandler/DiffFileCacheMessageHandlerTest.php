<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\MessageHandler\DiffFileCacheMessageHandler;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\DiffFileCacheMessageHandler
 * @covers ::__construct
 */
class DiffFileCacheMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject       $reviewRepository;
    private ReviewDiffServiceInterface&MockObject $diffService;
    private DiffFileCacheMessageHandler           $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->diffService      = $this->createMock(ReviewDiffServiceInterface::class);
        $this->messageHandler   = new DiffFileCacheMessageHandler($this->reviewRepository, $this->diffService);
        $this->messageHandler->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @covers ::handleEvent
     * @throws Throwable
     */
    public function testHandleEventMissingReview(): void
    {
        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->diffService->expects(self::never())->method('getDiffForRevisions');

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @covers ::handleEvent
     * @throws Throwable
     */
    public function testHandleEventNoRevisions(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects(self::never())->method('getDiffForRevisions');

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @covers ::handleEvent
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

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @covers ::handleEvent
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

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }

    /**
     * @covers ::handleEvent
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

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects(self::once())->method('getDiffForRevisions')->with($repository, [$revision])->willReturn([$file]);

        $this->messageHandler->handleEvent(new ReviewCreated(123, 456));
    }
}
