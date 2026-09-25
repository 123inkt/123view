<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\MessageHandler\Gitlab\CommentReplyAddedMessageHandler;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentReplyService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentReplyAddedMessageHandler::class)]
class CommentReplyAddedMessageHandlerTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject    $replyRepository;
    private GitlabApiProvider&MockObject         $apiProvider;
    private GitlabCommentReplyService&MockObject $commentService;
    private CommentReplyAddedMessageHandler      $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->apiProvider     = $this->createMock(GitlabApiProvider::class);
        $this->commentService  = $this->createMock(GitlabCommentReplyService::class);
        $this->handler         = new CommentReplyAddedMessageHandler(
            true,
            $this->replyRepository,
            $this->apiProvider,
            $this->commentService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeSkipIfDisabled(): void
    {
        $this->replyRepository->expects($this->never())->method('find');
        $this->apiProvider->expects($this->never())->method('create');
        $this->commentService->expects($this->never())->method('create');

        $handler = new CommentReplyAddedMessageHandler(
            false,
            $this->replyRepository,
            $this->apiProvider,
            $this->commentService
        );
        ($handler)(new CommentReplyAdded(111, 222, 333, 'message', 'file'));
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

        $reply = new CommentReply();
        $reply->setComment($comment);
        $reply->setUser($user);

        $this->replyRepository->expects($this->once())->method('find')->with(222)->willReturn($reply);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn(null);
        $this->commentService->expects($this->never())->method('create');

        ($this->handler)(new CommentReplyAdded(111, 222, 333, 'message', 'file'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeReplyAdded(): void
    {
        $user       = new User();
        $repository = new Repository();

        $review = new CodeReview();
        $review->setRepository($repository);

        $comment = new Comment();
        $comment->setReview($review);

        $reply = new CommentReply();
        $reply->setComment($comment);
        $reply->setUser($user);

        $api = static::createStub(GitlabApi::class);

        $this->replyRepository->expects($this->once())->method('find')->with(222)->willReturn($reply);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($api);
        $this->commentService->expects($this->once())->method('create')->with($api, $reply);

        ($this->handler)(new CommentReplyAdded(111, 222, 333, 'message', 'file'));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeReplyUpdated(): void
    {
        $user       = new User();
        $repository = new Repository();

        $review = new CodeReview();
        $review->setRepository($repository);

        $comment = new Comment();
        $comment->setReview($review);

        $reply = new CommentReply();
        $reply->setComment($comment);
        $reply->setUser($user);

        $api = static::createStub(GitlabApi::class);

        $this->replyRepository->expects($this->once())->method('find')->with(222)->willReturn($reply);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($api);
        $this->commentService->expects($this->once())->method('update')->with($api, $reply);

        ($this->handler)(new CommentReplyUpdated(111, 222, 333, 'message'));
    }
}
