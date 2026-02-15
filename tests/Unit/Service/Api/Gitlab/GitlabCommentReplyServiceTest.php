<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use PHPUnit\Framework\MockObject\Stub;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabCommentReplyService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(GitlabCommentReplyService::class)]
class GitlabCommentReplyServiceTest extends AbstractTestCase
{
    private GitlabApi&Stub              $api;
    private Discussions&MockObject            $discussions;
    private CommentReplyRepository&MockObject $replyRepository;
    private GitlabCommentReplyService         $service;
    private CommentReply                      $reply;
    private Comment                           $comment;
    private Repository                        $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new Repository();
        $review           = new CodeReview();
        $this->comment    = new Comment();
        $this->reply      = new CommentReply();
        $review->setRepository($this->repository);
        $this->comment->setReview($review);
        $this->reply->setComment($this->comment);
        $this->discussions = $this->createMock(Discussions::class);
        $this->api         = static::createStub(GitlabApi::class);
        $this->api->method('discussions')->willReturn($this->discussions);
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->service         = new GitlabCommentReplyService($this->replyRepository);
    }

    /**
     * @throws Throwable
     */
    public function testCreateSkipMissingReferenceId(): void
    {
        $this->discussions->expects($this->never())->method('createNote');
        $this->replyRepository->expects($this->never())->method('save');
        $this->service->create($this->api, $this->reply);
    }

    /**
     * @throws Throwable
     */
    public function testCreate(): void
    {
        $this->reply->setMessage('message');
        $this->comment->setExtReferenceId('111:222:333');
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '444'));

        $this->discussions->expects($this->once())->method('createNote')->with(444, 111, 222, 'message')->willReturn('123');
        $this->replyRepository->expects($this->once())->method('save')->with($this->reply, true);

        $this->service->create($this->api, $this->reply);
        static::assertSame('123', $this->reply->getExtReferenceId());
    }

    /**
     * @throws Throwable
     */
    public function testUpdateSkipIfMissingReferenceId(): void
    {
        $this->discussions->expects($this->never())->method('updateNote');
        $this->replyRepository->expects($this->never())->method('save');
        $this->service->update($this->api, $this->reply);
    }

    /**
     * @throws Throwable
     */
    public function testUpdate(): void
    {
        $this->reply->setMessage('message');
        $this->reply->setExtReferenceId('111:222:333');
        $this->repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '444'));

        $this->discussions->expects($this->once())->method('updateNote')->with(444, 111, '222', '333', 'message');
        $this->replyRepository->expects($this->never())->method('save');

        $this->service->update($this->api, $this->reply);
    }
}
