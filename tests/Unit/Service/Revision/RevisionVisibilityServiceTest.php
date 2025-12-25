<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionVisibility;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RevisionVisibilityService::class)]
class RevisionVisibilityServiceTest extends AbstractTestCase
{
    private RevisionVisibilityRepository&MockObject $visibilityRepository;
    private UserEntityProvider&MockObject           $userProvider;
    private RevisionVisibilityService               $service;
    private User                                    $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
        $this->user->setId(789);
        $this->visibilityRepository = $this->createMock(RevisionVisibilityRepository::class);
        $this->userProvider         = $this->createMock(UserEntityProvider::class);
        $this->service              = new RevisionVisibilityService($this->userProvider, $this->visibilityRepository);
    }

    public function testGetVisibleRevisionsWithoutVisibility(): void
    {
        $revision = new Revision();
        $review   = (new CodeReview())->setId(123);

        $this->userProvider->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->visibilityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123, 'user' => 789])
            ->willReturn([]);

        static::assertSame([$revision], $this->service->getVisibleRevisions($review, [$revision]));
    }

    public function testGetVisibleRevisions(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionB = new Revision();
        $revisionB->setId(457);
        $revisionC = new Revision();
        $revisionC->setId(458);

        $review = new CodeReview();
        $review->setId(123);

        $visibilityA = new RevisionVisibility();
        $visibilityA->setRevision($revisionA)->setVisible(true);
        $visibilityB = new RevisionVisibility();
        $visibilityB->setRevision($revisionB)->setVisible(false);

        $this->userProvider->expects($this->once())->method('getUser')->willReturn($this->user);
        $this->visibilityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123, 'user' => 789])
            ->willReturn([$visibilityA, $visibilityB]);
        $result = $this->service->getVisibleRevisions($review, [$revisionA, $revisionB, $revisionC]);

        // revisionB is not visible
        static::assertSame([$revisionA, $revisionC], $result);
    }

    public function testGetRevisionVisibilities(): void
    {
        $revisionA = new Revision();
        $revisionA->setId(456);
        $revisionB = new Revision();
        $revisionB->setId(457);

        $review = new CodeReview();
        $review->setId(123);

        $visibilityA = new RevisionVisibility();
        $visibilityA->setRevision($revisionA)->setVisible(false);

        $this->visibilityRepository->expects($this->once())
            ->method('findBy')
            ->with(['review' => 123, 'user' => 789])
            ->willReturn([$visibilityA]);

        $result = $this->service->getRevisionVisibilities($review, [$revisionA, $revisionB], $this->user);
        static::assertCount(2, $result);
        static::assertSame($visibilityA, $result[0]);
        static::assertTrue($result[1]->isVisible());
    }

    public function testSetRevisionVisibility(): void
    {
        $revision = new Revision();
        $revision->setId(456);

        $review = new CodeReview();
        $review->setId(123);

        $visibility = new RevisionVisibility();
        $visibility->setRevision($revision);
        $visibility->setVisible(true);

        $this->visibilityRepository->expects($this->once())->method('findBy')->with(['review' => 123, 'user' => 789])->willReturn([$visibility]);
        $this->visibilityRepository->expects($this->once())->method('saveAll')->with([$visibility], true);

        $this->service->setRevisionVisibility($review, [$revision], $this->user, false);
    }

    public function testSetRevisionVisibilityShouldSkipEmptyRevisions(): void
    {
        $review = new CodeReview();
        $review->setId(123);

        $this->visibilityRepository->expects($this->never())->method('findBy');
        $this->visibilityRepository->expects($this->never())->method('saveAll');

        $this->service->setRevisionVisibility($review, [], $this->user, false);
    }
}
