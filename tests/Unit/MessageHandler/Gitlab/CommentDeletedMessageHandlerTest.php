<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\MessageHandler\Gitlab\CommentDeletedMessageHandler;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentDeletedMessageHandler::class)]
class CommentDeletedMessageHandlerTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private UserRepository&MockObject       $userRepository;
    private GitlabApiProvider&MockObject    $apiProvider;
    private GitlabCommentService&MockObject $commentService;
    private CommentDeletedMessageHandler    $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->userRepository   = $this->createMock(UserRepository::class);
        $this->apiProvider      = $this->createMock(GitlabApiProvider::class);
        $this->commentService   = $this->createMock(GitlabCommentService::class);
        $this->handler          = new CommentDeletedMessageHandler(
            true,
            $this->reviewRepository,
            $this->userRepository,
            $this->apiProvider,
            $this->commentService
        );
    }

    /**
     * @throws Throwable
     */
    public function testInvokeSkipIfDisabled(): void
    {
        $this->reviewRepository->expects($this->never())->method('find');
        $this->userRepository->expects($this->never())->method('find');
        $this->apiProvider->expects($this->never())->method('create');
        $this->commentService->expects($this->never())->method('delete');

        $handler = new CommentDeletedMessageHandler(
            false,
            $this->reviewRepository,
            $this->userRepository,
            $this->apiProvider,
            $this->commentService
        );
        ($handler)(new CommentRemoved(111, 222, 333, 'file', 'message', 'referenceId'));
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

        $this->reviewRepository->expects($this->once())->method('find')->with(111)->willReturn($review);
        $this->userRepository->expects($this->once())->method('find')->with(333)->willReturn($user);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn(null);
        $this->commentService->expects($this->never())->method('delete');

        ($this->handler)(new CommentRemoved(111, 222, 333, 'file', 'message', 'referenceId'));
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

        $api = static::createStub(GitlabApi::class);

        $this->reviewRepository->expects($this->once())->method('find')->with(111)->willReturn($review);
        $this->userRepository->expects($this->once())->method('find')->with(333)->willReturn($user);
        $this->apiProvider->expects($this->once())->method('create')->with($repository, $user)->willReturn($api);
        $this->commentService->expects($this->once())->method('delete')->with($api, $repository, 'referenceId');

        ($this->handler)(new CommentRemoved(111, 222, 333, 'file', 'message', 'referenceId'));
    }
}
