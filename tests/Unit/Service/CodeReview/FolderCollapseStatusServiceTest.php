<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Entity\Review\FolderCollapseStatusCollection;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Service\CodeReview\FolderCollapseStatusService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(FolderCollapseStatusService::class)]
class FolderCollapseStatusServiceTest extends AbstractTestCase
{
    private FolderCollapseStatusRepository&MockObject $statusRepository;
    private User                                      $user;
    private FolderCollapseStatusService               $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statusRepository = $this->createMock(FolderCollapseStatusRepository::class);
        $this->user             = (new User())->setId(456);
        $this->service          = new FolderCollapseStatusService($this->statusRepository, $this->user);
    }

    public function testGetFolderCollapseStatus(): void
    {
        $status = new FolderCollapseStatus();

        $review = (new CodeReview())->setId(123);
        $this->statusRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123, 'user' => 456])
            ->willReturn([$status]);

        static::assertEquals(new FolderCollapseStatusCollection([$status]), $this->service->getFolderCollapseStatus($review));
    }
}
