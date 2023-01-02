<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;
use DR\Review\Message\Revision\CommitRemovedMessage;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\MessageHandler\CommitRemovedMessageHandler;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * @coversDefaultClass \DR\Review\MessageHandler\CommitRemovedMessageHandler
 * @covers ::__construct
 */
class CommitRemovedMessageHandlerTest extends AbstractTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private RevisionRepository&MockObject   $revisionRepository;
    private CodeReviewRepository&MockObject $reviewRepository;
    private MessageBusInterface&MockObject  $bus;
    private CommitRemovedMessageHandler     $messageHandler;
    private Envelope                        $envelope;

    public function setUp(): void
    {
        parent::setUp();
        $this->envelope             = new Envelope(new stdClass(), []);
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->revisionRepository   = $this->createMock(RevisionRepository::class);
        $this->reviewRepository     = $this->createMock(CodeReviewRepository::class);
        $this->bus                  = $this->createMock(MessageBusInterface::class);
        $this->messageHandler       = new CommitRemovedMessageHandler(
            $this->repositoryRepository,
            $this->revisionRepository,
            $this->reviewRepository,
            $this->bus
        );
    }

    /**
     * @covers ::__invoke
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
     * @covers ::__invoke
     * @throws Throwable
     */
    public function testInvokeWithReview(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $review = new CodeReview();
        $review->setId(789);
        $revision = new Revision();
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
        $this->bus->expects(self::once())->method('dispatch')->with(new ReviewRevisionRemoved(789, 456, null, 'title'))->willReturn($this->envelope);
        $this->revisionRepository->expects(self::once())->method('remove')->with($revision, true);

        ($this->messageHandler)(new CommitRemovedMessage(123, 'hash'));

        static::assertCount(0, $review->getRevisions());
        static::assertNull($revision->getReview());
    }

    /**
     * @covers ::__invoke
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
        $this->bus->expects(self::never())->method('dispatch');
        $this->revisionRepository->expects(self::once())->method('remove')->with($revision, true);

        ($this->messageHandler)(new CommitRemovedMessage(123, 'hash'));
    }
}
