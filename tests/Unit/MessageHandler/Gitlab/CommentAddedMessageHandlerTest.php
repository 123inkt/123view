<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\MessageHandler\Gitlab\CommentAddedMessageHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Review\Service\Api\Gitlab\ReviewMergeRequestService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Throwable;

#[CoversClass(CommentAddedMessageHandler::class)]
class CommentAddedMessageHandlerTest extends AbstractTestCase
{
    private CommentRepository&MockObject         $commentRepository;
    private GitlabApiProvider&MockObject         $apiProvider;
    private ReviewMergeRequestService&MockObject $mergeRequestService;
    private GitlabCommentService&MockObject      $commentService;
    private CommentAddedMessageHandler           $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository   = $this->createMock(CommentRepository::class);
        $this->apiProvider         = $this->createMock(GitlabApiProvider::class);
        $this->mergeRequestService = $this->createMock(ReviewMergeRequestService::class);
        $this->commentService      = $this->createMock(GitlabCommentService::class);
        $this->handler             = new CommentAddedMessageHandler(
            true,
            $this->commentRepository,
            $this->apiProvider,
            $this->mergeRequestService,
            $this->commentService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeSkipIfDisabled(): void
    {
        $this->commentRepository->expects($this->never())->method('find');

        $handler = new CommentAddedMessageHandler(
            false,
            $this->commentRepository,
            $this->apiProvider,
            $this->mergeRequestService,
            $this->commentService
        );
        ($handler)(new CommentAdded(111, 222, 333, 'file', 'message'));
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

        ($this->handler)(new CommentAdded(111, 222, 333, 'file', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeNoMergeRequestId(): void
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
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($api, $review)->willReturn(null);

        ($this->handler)(new CommentAdded(111, 222, 333, 'file', 'message'));
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
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($api, $review)->willReturn(12345);
        $this->commentService->expects($this->once())->method('create')->with($api, $comment, 12345);
        $this->commentService->expects($this->never())->method('updateExtReferenceId');

        ($this->handler)(new CommentAdded(111, 222, 333, 'file', 'message'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeFailureRetry(): void
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
        $this->mergeRequestService->expects($this->once())->method('retrieveMergeRequestIID')->with($api, $review)->willReturn(12345);
        $this->commentService->expects($this->once())->method('create')->with($api, $comment, 12345)->willThrowException(new RuntimeException('foo'));
        $this->commentService->expects($this->once())->method('updateExtReferenceId')->with($api, $comment, 12345);

        ($this->handler)(new CommentAdded(111, 222, 333, 'file', 'message'));
    }
}
