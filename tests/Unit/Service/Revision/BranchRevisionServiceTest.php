<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Review\Service\Revision\BranchRevisionService;
use DR\Review\Service\Revision\RevisionSorter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(BranchRevisionService::class)]
class BranchRevisionServiceTest extends AbstractTestCase
{
    private CacheableGitRevListService&MockObject $revListService;
    private RevisionRepository&MockObject         $revisionRepository;
    private RevisionSorter&MockObject             $revisionSorter;
    private BranchRevisionService                 $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revListService     = $this->createMock(CacheableGitRevListService::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->revisionSorter     = $this->createMock(RevisionSorter::class);
        $this->service            = new BranchRevisionService($this->revListService, $this->revisionRepository, $this->revisionSorter);
    }

    public function testGetRevisionsForBranch(): void
    {
        $repository = new Repository();
        $revisionA  = new Revision()->setId(10);
        $revisionB  = new Revision()->setId(20);
        $hashes     = ['sha-a', 'sha-b'];

        $this->revListService->expects($this->once())
            ->method('getCommitsAheadOf')
            ->with($repository, 'origin/feature', 'main')
            ->willReturn($hashes);
        $this->revisionRepository->expects($this->once())
            ->method('findBy')
            ->with(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC'])
            ->willReturn([$revisionA, $revisionB]);
        $this->revisionSorter->expects($this->once())
            ->method('sort')
            ->with([10 => $revisionA, 20 => $revisionB])
            ->willReturnArgument(0);

        static::assertSame([10 => $revisionA, 20 => $revisionB], $this->service->getRevisionsFor($repository, 'origin/feature', 'main'));
    }
}
