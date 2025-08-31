<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FileSeenStatus;
use DR\Review\Entity\Review\FileSeenStatusCollection;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FileSeenStatusRepository;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\DiffTree\LockableGitDiffTreeService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FileSeenStatusService::class)]
class FileSeenStatusServiceTest extends AbstractTestCase
{
    private LockableGitDiffTreeService&MockObject $treeService;
    private FileSeenStatusRepository&MockObject   $statusRepository;
    private UserEntityProvider&MockObject         $userProvider;
    private FileSeenStatusService                 $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->treeService      = $this->createMock(LockableGitDiffTreeService::class);
        $this->statusRepository = $this->createMock(FileSeenStatusRepository::class);
        $this->userProvider     = $this->createMock(UserEntityProvider::class);
        $this->service          = new FileSeenStatusService($this->treeService, $this->statusRepository, $this->userProvider);
    }

    public function testGetFileSeenStatus(): void
    {
        $user   = (new User())->setId(123);
        $review = (new CodeReview())->setId(456);
        $status = new FileSeenStatus();

        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->statusRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 456, 'user' => 123])
            ->willReturn([$status]);

        $result = $this->service->getFileSeenStatus($review);

        static::assertInstanceOf(FileSeenStatusCollection::class, $result);
    }
}
