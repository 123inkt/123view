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
use DR\Review\Service\Api\Gitlab\GitlabCommentFormatter;
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
    private PositionFactory&MockObject        $positionFactory;
    private CommentRepository&MockObject      $commentRepository;
    private GitlabCommentFormatter&MockObject $commentFormatter;
    private GitlabApi&MockObject              $api;
    private MergeRequests&MockObject          $mergeRequests;
    private Discussions&MockObject            $discussions;
    private Comment                           $comment;
    private CodeReview                        $review;
    private Repository                        $repository;
    private GitlabCommentService              $service;

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
        $this->commentFormatter  = $this->createMock(GitlabCommentFormatter::class);
        $this->service           = new GitlabCommentService($this->positionFactory, $this->commentRepository, $this->commentFormatter);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithExistingReferenceId(): void
    {
        $this->comment->setExtReferenceId('external-reference-id');
        $this->mergeRequests->expects($this->never())->method('versions');
        $this->service->create($this->api, $this->comment, 456);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutVersion(): void
    {
        $this->comment->setReview($this->review);
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $this->mergeRequests->expects($this->once())->method('versions')->with(123, 456)->willReturn([]);
        $this->positionFactory->expects($this->never())->method('create');

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

        $this->mergeRequests->expects($this->once())->method('versions')->with(123, 456)->willReturn([$version]);
        $this->positionFactory->expects($this->once())->method('create')->with($version, $lineReference)->willReturn($position);
        $this->commentFormatter->expects($this->once())->method('format')->with($this->comment)->willReturn('formatted');
        $this->discussions->expects($this->once())->method('createDiscussion')->with(123, 456, $position, 'formatted')->willReturn('1:2:3');
        $this->commentRepository->expects($this->once())->method('save')->with($this->comment, true);

        $this->service->create($this->api, $this->comment, 456);
        static::assertSame('1:2:3', $this->comment->getExtReferenceId());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateExtReferenceId(): void
    {
        $lineReference = new LineReference('old', 'new', 1, 2, 3, null, LineReferenceStateEnum::Added);
        $this->comment->setReview($this->review);
        $this->comment->setLineReference($lineReference);
        $this->comment->setMessage('match');
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));

        $threads = [
            ['id' => '1', 'notes' => [['id' => '2', 'body' => 'foobar', 'position' => ['old_path' => 'old', 'new_path' => 'new']]]],
            ['id' => '2', 'notes' => [['id' => '2', 'body' => 'match', 'position' => ['old_path' => 'foo', 'new_path' => 'bar']]]],
            ['id' => '3', 'notes' => [['id' => '2', 'body' => 'match', 'position' => ['old_path' => 'old', 'new_path' => 'new']]]]
        ];

        $this->discussions->expects($this->once())->method('getDiscussions')->with(123, 456)->willReturn(static::createGeneratorFrom($threads));
        $this->commentRepository->expects($this->once())->method('save')->with($this->comment, true);

        $this->service->updateExtReferenceId($this->api, $this->comment, 456);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateAbsentReferenceId(): void
    {
        $this->comment->setExtReferenceId(null);
        $this->discussions->expects($this->never())->method('updateNote');

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

        $this->commentFormatter->expects($this->once())->method('format')->with($this->comment)->willReturn('formatted');
        $this->discussions->expects($this->once())->method('updateNote')->with(111, 222, '333', '444', 'formatted');

        $this->service->update($this->api, $this->comment);
    }

    /**
     * @throws Throwable
     */
    public function testResolveAbsentReferenceId(): void
    {
        $this->comment->setExtReferenceId(null);
        $this->discussions->expects($this->never())->method('resolve');

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

        $this->discussions->expects($this->once())->method('resolve')->with(111, 222, '333', true);

        $this->service->resolve($this->api, $this->comment, true);
    }

    /**
     * @throws Throwable
     */
    public function testDelete(): void
    {
        $this->review->setRepository($this->repository);
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '111'));

        $this->discussions->expects($this->once())->method('deleteNote')->with(111, 222, '333', '444');

        $this->service->delete($this->api, $this->repository, '222:333:444');
    }
}
