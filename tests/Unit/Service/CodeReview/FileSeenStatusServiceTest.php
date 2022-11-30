<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\FileSeenStatus;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService
 * @covers ::__construct
 */
class FileSeenStatusServiceTest extends AbstractTestCase
{
    private FileSeenStatusRepository&MockObject $statusRepository;
    private User                                $user;
    private FileSeenStatusService               $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->statusRepository = $this->createMock(FileSeenStatusRepository::class);
        $this->user             = new User();
        $this->service          = new FileSeenStatusService($this->statusRepository, $this->user);
    }

    /**
     * @covers ::markAsSeen
     */
    public function testMarkAsSeenWithFileIsNull(): void
    {
        $review = new CodeReview();
        $user   = new User();

        $this->statusRepository->expects(self::never())->method('save');
        $this->service->markAsSeen($review, $user, null);
    }

    /**
     * @covers ::markAsSeen
     */
    public function testMarkAsSeen(): void
    {
        $review   = new CodeReview();
        $user     = new User();
        $filepath = 'filepath';

        $this->statusRepository->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    static function (FileSeenStatus $status) use ($review, $user, $filepath) {
                        static::assertSame($review, $status->getReview());
                        static::assertSame($user, $status->getUser());
                        static::assertSame($filepath, $status->getFilePath());

                        return true;
                    }
                )
            );
        $this->service->markAsSeen($review, $user, $filepath);
    }

    /**
     * @covers ::markAsUnseen
     */
    public function testMarkAsUnseenWithoutFile(): void
    {
        $review = new CodeReview();
        $user   = new User();

        $this->statusRepository->expects(self::never())->method('remove');
        $this->service->markAsUnseen($review, $user, null);
    }

    /**
     * @covers ::markAsUnseen
     */
    public function testMarkAsUnseenNonExistingFile(): void
    {
        $review   = new CodeReview();
        $user     = new User();
        $filepath = 'filepath';

        $this->statusRepository->expects(self::once())->method('findOneBy')->willReturn(null);
        $this->statusRepository->expects(self::never())->method('remove');
        $this->service->markAsUnseen($review, $user, $filepath);
    }

    /**
     * @covers ::markAsUnseen
     */
    public function testMarkAsUnseen(): void
    {
        $review = new CodeReview();
        $review->setId(123);
        $user = new User();
        $user->setId(456);
        $filepath = 'filepath';
        $status   = new FileSeenStatus();

        $this->statusRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['review' => 123, 'user' => 456, 'filePath' => 'filepath'])
            ->willReturn($status);
        $this->statusRepository->expects(self::once())->method('remove')->with($status);
        $this->service->markAsUnseen($review, $user, $filepath);
    }

    /**
     * @covers ::markAllAsUnseen
     */
    public function testMarkAllAsUnseen(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $fileA                 = new DiffFile();
        $fileA->filePathBefore = 'filePathBefore';
        $fileA->filePathAfter  = 'filePathAfter';
        $fileB                 = new DiffFile();
        $fileB->filePathBefore = 'filePathBefore';
        $fileB->filePathAfter  = null;

        $statusA = new FileSeenStatus();
        $statusB = new FileSeenStatus();

        $this->statusRepository->expects(self::once())
            ->method('findBy')
            ->with(['review' => 123, 'filePath' => ['filePathBefore', 'filePathAfter']])
            ->willReturn([$statusA, $statusB]);
        $this->statusRepository->expects(self::exactly(2))->method('remove')->withConsecutive([$statusA, false], [$statusB, true]);

        $this->service->markAllAsUnseen($review, [$fileA, $fileB]);
    }

    /**
     * @covers ::getFileSeenStatus
     */
    public function testGetFileSeenStatus(): void
    {
        $review = new CodeReview();
        $review->setId(123);
        $this->user->setId(456);

        $status = new FileSeenStatus();

        $this->statusRepository->expects(self::once())
            ->method('findBy')
            ->with(['review' => 123, 'user' => 456])
            ->willReturn([$status]);

        $collection = $this->service->getFileSeenStatus($review);
        static::assertCount(1, $collection);
        static::assertTrue($collection->contains($status));
    }
}
