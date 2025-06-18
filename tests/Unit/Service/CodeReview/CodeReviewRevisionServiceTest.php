<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\RevList\CacheableGitRevListService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewRevisionService::class)]
class CodeReviewRevisionServiceTest extends AbstractTestCase
{
    private CacheableGitRevListService&MockObject $revListService;
    private RevisionRepository&MockObject         $revisionRepository;
    private CodeReviewRevisionService             $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revListService     = $this->createMock(CacheableGitRevListService::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->service            = new CodeReviewRevisionService($this->revListService, $this->revisionRepository);
    }

    public function testGetRevisionsCommitsReview(): void
    {
        $revision = new Revision();
        $review   = new CodeReview();
        $review->setType(CodeReviewType::COMMITS);
        $review->getRevisions()->add($revision);

        static::assertSame([$revision], $this->service->getRevisions($review));
    }

    public function testGetRevisionsCommitsForBranch(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setId(123);
        $review->setReferenceId('branch_name');
        $review->setRepository($repository);
        $review->setType(CodeReviewType::BRANCH);
        $hashes = ['sha1', 'sha2'];

        $this->revListService->expects($this->once())->method('getCommitsAheadOf')->with($repository, 'branch_name')->willReturn($hashes);
        $this->revisionRepository->expects($this->once())
            ->method('findBy')
            ->with(['repository' => $repository, 'commitHash' => $hashes], ['createTimestamp' => 'ASC'])
            ->willReturn([$revision]);

        // invoke twice, second call _should_ be the internally stored hit
        static::assertSame([456 => $revision], $this->service->getRevisions($review));
        static::assertSame([456 => $revision], $this->service->getRevisions($review));
    }

    public function testGetRevisionsCommitsForBranchWithException(): void
    {
        $revision = new Revision();
        $revision->setId(456);
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setId(123);
        $review->setReferenceId('branch_name');
        $review->setRepository($repository);
        $review->setType(CodeReviewType::BRANCH);

        $this->revListService->expects($this->once())->method('getCommitsAheadOf')
            ->with($repository, 'branch_name')
            ->willThrowException(new RepositoryException());
        $this->revisionRepository->expects(self::never())->method('findBy');

        static::assertSame([], $this->service->getRevisions($review));
    }
}
