<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Service\CodeReview\FolderCollapseStatusService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FolderCollapseStatusService::class)]
class FolderCollapseStatusServiceTest extends AbstractTestCase
{
    private FolderCollapseStatusRepository&MockObject $statusRepository;
    private UserEntityProvider&MockObject             $userProvider;
    private FolderCollapseStatusService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statusRepository = $this->createMock(FolderCollapseStatusRepository::class);
        $this->userProvider     = $this->createMock(UserEntityProvider::class);
        $this->service          = new FolderCollapseStatusService($this->statusRepository, $this->userProvider);
    }

    public function testGetFolderCollapseStatus(): void
    {
        $status = new FolderCollapseStatus();
        $user   = (new User())->setId(456);
        $review = (new CodeReview())->setId(123);

        $this->userProvider->expects($this->once())->method('getUser')->willReturn($user);
        $this->statusRepository->expects($this->once())->method('findBy')->with(['review' => 123, 'user' => 456])->willReturn([$status]);

        static::assertEquals(new FolderCollapseStatusCollection([$status]), $this->service->getFolderCollapseStatus($review));
    }
}
