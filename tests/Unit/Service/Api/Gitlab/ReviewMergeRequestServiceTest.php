<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Service\Api\Gitlab\ReviewMergeRequestService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewMergeRequestService::class)]
class ReviewMergeRequestServiceTest extends AbstractTestCase
{
    private GitlabApi&MockObject            $api;
    private MergeRequests&MockObject        $mergeRequests;
    private CodeReviewRepository&MockObject $reviewRepository;
    private ReviewMergeRequestService       $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mergeRequests = $this->createMock(MergeRequests::class);
        $this->api           = $this->createMock(GitlabApi::class);
        $this->api->method('mergeRequests')->willReturn($this->mergeRequests);
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->service          = new ReviewMergeRequestService($this->reviewRepository);
        $this->service->setLogger($this->logger);
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveMergeRequestIIDWithExtReferenceId(): void
    {
        $review = new CodeReview();
        $review->setExtReferenceId('1234');

        static::assertSame(1234, $this->service->retrieveMergeRequestIID($this->api, $review));
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveMergeRequestIIDForUnknownBranch(): void
    {
        $revision = new Revision();
        $revision->setFirstBranch(null);

        $review = new CodeReview();
        $review->setExtReferenceId(null);
        $review->getRevisions()->add($revision);

        $this->logger->expects($this->once())->method('info')->with('No branch name found for review {id}');

        static::assertNull($this->service->retrieveMergeRequestIID($this->api, $review));
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveMergeRequestIIDForUnknownMergeRequest(): void
    {
        $revision = new Revision();
        $revision->setFirstBranch('remote-branch');

        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '1234'));

        $review = new CodeReview();
        $review->setExtReferenceId(null);
        $review->getRevisions()->add($revision);
        $review->setRepository($repository);

        $this->logger->expects($this->once())->method('info')->with('No merge request found for review {id} - {ref}');
        $this->mergeRequests->expects($this->once())->method('findByRemoteRef')->with(1234, 'remote-branch')->willReturn(null);

        static::assertNull($this->service->retrieveMergeRequestIID($this->api, $review));
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveMergeRequestIID(): void
    {
        $revision = new Revision();
        $revision->setFirstBranch('remote-branch');

        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '1234'));

        $review = new CodeReview();
        $review->setExtReferenceId(null);
        $review->setType(CodeReviewType::COMMITS);
        $review->getRevisions()->add($revision);
        $review->setRepository($repository);

        $this->mergeRequests->expects($this->once())->method('findByRemoteRef')->with(1234, 'remote-branch')->willReturn(['iid' => 1111]);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);

        static::assertSame(1111, $this->service->retrieveMergeRequestIID($this->api, $review));
    }

    /**
     * @throws Throwable
     */
    public function testRetrieveMergeRequestIIDForBranchReview(): void
    {
        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '1234'));

        $review = new CodeReview();
        $review->setType(CodeReviewType::BRANCH);
        $review->setTargetBranch('remote-branch');
        $review->setExtReferenceId(null);
        $review->setRepository($repository);

        $this->mergeRequests->expects($this->once())->method('findByRemoteRef')->with(1234, 'remote-branch')->willReturn(['iid' => 1111]);
        $this->reviewRepository->expects($this->once())->method('save')->with($review, true);

        static::assertSame(1111, $this->service->retrieveMergeRequestIID($this->api, $review));
    }
}
