<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\MessageHandler\Gitlab\CommentUpdatedMessageHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentUpdatedMessageHandler::class)]
class CommentUpdatedMessageHandlerTest extends AbstractTestCase
{
    private CommentRepository&MockObject    $commentRepository;
    private GitlabApiProvider&MockObject    $apiProvider;
    private GitlabCommentService&MockObject $commentService;
    private CommentUpdatedMessageHandler    $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->apiProvider       = $this->createMock(GitlabApiProvider::class);
        $this->commentService    = $this->createMock(GitlabCommentService::class);
        $this->handler           = new CommentUpdatedMessageHandler(
            true,
            $this->commentRepository,
            $this->apiProvider,
            $this->commentService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeSkipIfDisabled(): void
    {
        $this->commentRepository->expects($this->never())->method('find');

        $handler = new CommentUpdatedMessageHandler(
            false,
            $this->commentRepository,
            $this->apiProvider,
            $this->commentService
        );
        ($handler)(new CommentUpdated(111, 222, 333, 'file', 'message', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeNoApi(): void
    {
        $user       = new User();
        $repository = new Repository();

        $review = new CodeReview();
        $review->setRepository($repository);

        $comment = new Comment();
        $comment->setReview($review);
        $comment->setUser($user);

        $this->commentRepository->expects($this->once())->method('find')->with(222)->willReturn($comment);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn(null);

        ($this->handler)(new CommentUpdated(111, 222, 333, 'file', 'message', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeSuccess(): void
    {
        $user       = new User();
        $repository = new Repository();

        $review = new CodeReview();
        $review->setRepository($repository);

        $comment = new Comment();
        $comment->setReview($review);
        $comment->setUser($user);

        $api = $this->createMock(GitlabApi::class);

        $this->commentRepository->expects($this->once())->method('find')->with(222)->willReturn($comment);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($api);
        $this->commentService->expects($this->once())->method('update')->with($api, $comment);

        ($this->handler)(new CommentUpdated(111, 222, 333, 'file', 'message', 'message'));
    }
}
