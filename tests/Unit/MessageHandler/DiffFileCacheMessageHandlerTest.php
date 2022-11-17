<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\MessageHandler\DiffFileCacheMessageHandler;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\CodeHighlight\CacheableHighlightedFileService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\DiffFileCacheMessageHandler
 * @covers ::__construct
 */
class DiffFileCacheMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject            $reviewRepository;
    private ReviewDiffServiceInterface&MockObject      $diffService;
    private CacheableHighlightedFileService&MockObject $fileService;
    private DiffFileCacheMessageHandler                $messageHandler;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->diffService      = $this->createMock(ReviewDiffServiceInterface::class);
        $this->fileService      = $this->createMock(CacheableHighlightedFileService::class);
        $this->messageHandler   = new DiffFileCacheMessageHandler($this->reviewRepository, $this->diffService, $this->fileService);
    }

    /**
     * @covers ::handleEvent
     * @throws Throwable
     */
    public function testHandleEventMissingReview(): void
    {
        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn(null);
        $this->diffService->expects(self::never())->method('getDiffFiles');

        $this->messageHandler->handleEvent(new ReviewCreated(123));
    }

    /**
     * @covers ::handleEvent
     * @throws Throwable
     */
    public function testHandleEventNoRevisions(): void
    {
        $review = new CodeReview();

        $this->reviewRepository->expects(self::once())->method('find')->with(123)->willReturn($review);
        $this->diffService->expects(self::never())->method('getDiffFiles');

        $this->messageHandler->handleEvent(new ReviewCreated(123));
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
        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$file]);
        $this->fileService->expects(self::once())->method('getHighlightedFile')->with($revision, 'file-path-after');

        $this->messageHandler->handleEvent(new ReviewCreated(123));
    }

    /**
     * @covers ::getHandledMessages
     */
    public function testGetHandledMessages(): void
    {
        $expected = [
            ReviewCreated::class         => ['method' => 'handleEvent', 'from_transport' => 'async_messages'],
            ReviewRevisionAdded::class   => ['method' => 'handleEvent', 'from_transport' => 'async_messages'],
            ReviewRevisionRemoved::class => ['method' => 'handleEvent', 'from_transport' => 'async_messages']
        ];

        $actual = [...DiffFileCacheMessageHandler::getHandledMessages()];
        static::assertSame($expected, $actual);
    }
}
