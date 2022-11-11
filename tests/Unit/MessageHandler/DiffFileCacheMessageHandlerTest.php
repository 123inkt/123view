<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\MessageHandler;

use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\MessageHandler\DiffFileCacheMessageHandler;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\MessageHandler\DiffFileCacheMessageHandler
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
    }

    /**
     * @covers ::handleEvent
     */
    public function testHandleEvent(): void
    {
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
