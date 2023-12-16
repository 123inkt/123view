<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\Version;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Service\Api\Gitlab\PositionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(GitlabCommentService::class)]
class GitlabCommentServiceTest extends AbstractTestCase
{
    private PositionFactory&MockObject   $positionFactory;
    private CommentRepository&MockObject $commentRepository;
    private GitlabApi&MockObject         $api;
    private MergeRequests&MockObject     $mergeRequests;
    private Discussions&MockObject       $discussions;
    private Comment                      $comment;
    private CodeReview                   $review;
    private Repository                   $repository;
    private GitlabCommentService         $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->comment       = new Comment();
        $this->review        = new CodeReview();
        $this->repository    = new Repository();
        $this->mergeRequests = $this->createMock(MergeRequests::class);
        $this->discussions   = $this->createMock(Discussions::class);
        $this->api           = $this->createMock(GitlabApi::class);
        $this->api->method('mergeRequests')->willReturn($this->mergeRequests);
        $this->api->method('discussions')->willReturn($this->discussions);
        $this->positionFactory   = $this->createMock(PositionFactory::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->service           = new GitlabCommentService($this->positionFactory, $this->commentRepository);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutVersion(): void
    {
        $this->comment->setReview($this->review);
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $this->mergeRequests->expects(self::once())->method('versions')->with(123, 456)->willReturn([]);
        $this->positionFactory->expects(self::never())->method('create');

        $this->service->create($this->api, $this->comment, 456);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithVersion(): void
    {
        $version       = new Version();
        $lineReference = new LineReference('old', 'new', 1, 2, 3, null, LineReferenceStateEnum::Added);
        $position      = new Position();
        $this->comment->setReview($this->review);
        $this->comment->setLineReference($lineReference);
        $this->comment->setMessage('message');
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $this->mergeRequests->expects(self::once())->method('versions')->with(123, 456)->willReturn([$version]);
        $this->positionFactory->expects(self::once())->method('create')->with($version, $lineReference)->willReturn($position);
        $this->discussions->expects(self::once())->method('create')->with(123, 456, $position, 'message')->willReturn('1:2:3');
        $this->commentRepository->expects(self::once())->method('save')->with($this->comment, true);

        $this->service->create($this->api, $this->comment, 456);
        static::assertSame('1:2:3', $this->comment->getExtReferenceId());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateAbsentReferenceId(): void
    {
        $this->comment->setExtReferenceId(null);
        $this->discussions->expects(self::never())->method('update');

        $this->service->update($this->api, $this->comment);
    }

    /**
     * @throws Throwable
     */
    public function testUpdate(): void
    {
        $this->comment->setExtReferenceId('222:333:444');
        $this->comment->setReview($this->review);
        $this->comment->setMessage('message');
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '111'));

        $this->discussions->expects(self::once())->method('update')->with(111, 222, '333', '444', 'message');

        $this->service->update($this->api, $this->comment);
    }

    /**
     * @throws Throwable
     */
    public function testResolveAbsentReferenceId(): void
    {
        $this->comment->setExtReferenceId(null);
        $this->discussions->expects(self::never())->method('resolve');

        $this->service->resolve($this->api, $this->comment, true);
    }

    /**
     * @throws Throwable
     */
    public function testResolve(): void
    {
        $this->comment->setExtReferenceId('222:333:444');
        $this->comment->setReview($this->review);
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '111'));

        $this->discussions->expects(self::once())->method('resolve')->with(111, 222, '333', true);

        $this->service->resolve($this->api, $this->comment, true);
    }

    /**
     * @throws Throwable
     */
    public function testDelete(): void
    {
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '111'));

        $this->discussions->expects(self::once())->method('delete')->with(111, 222, '333', '444');

        $this->service->delete($this->api, $this->repository, '222:333:444');
    }
}
