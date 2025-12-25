<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler\Gitlab;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\MessageHandler\Gitlab\CommentResolvedMessageHandler;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\GitlabCommentService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CommentResolvedMessageHandler::class)]
class CommentResolvedMessageHandlerTest extends AbstractTestCase
{
    private CommentRepository&MockObject    $commentRepository;
    private UserRepository&MockObject       $userRepository;
    private GitlabApiProvider&MockObject    $apiProvider;
    private GitlabCommentService&MockObject $commentService;
    private CommentResolvedMessageHandler   $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->userRepository    = $this->createMock(UserRepository::class);
        $this->apiProvider       = $this->createMock(GitlabApiProvider::class);
        $this->commentService    = $this->createMock(GitlabCommentService::class);
        $this->handler           = new CommentResolvedMessageHandler(
            true,
            $this->commentRepository,
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
        $this->commentRepository->expects($this->never())->method('find');

        $handler = new CommentResolvedMessageHandler(
            false,
            $this->commentRepository,
            $this->userRepository,
            $this->apiProvider,
            $this->commentService
        );
        ($handler)(new CommentResolved(111, 222, 333, 'file'));
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
        $comment->setUser($user);
        $comment->setReview($review);

        $this->commentRepository->expects($this->once())->method('find')->with(222)->willReturn($comment);
        $this->userRepository->expects($this->once())->method('find')->with(333)->willReturn($user);
        $this->apiProvider->expects($this->exactly(2))->method('create')->with($repository, $user)->willReturn(null);

        ($this->handler)(new CommentResolved(111, 222, 333, 'file'));
    }

    /**
     * @throws Throwable
     */
    #[TestWith([new CommentResolved(111, 222, 333, 'file'), true])]
    #[TestWith([new CommentUnresolved(111, 222, 333, 'file'), false])]
    public function testInvokeSuccess(CommentUnresolved|CommentResolved $event, bool $resolve): void
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
        $this->userRepository->expects($this->once())->method('find')->with(333)->willReturn($user);
        $this->commentService->expects($this->once())->method('resolve')->with($api, $comment, $resolve);

        ($this->handler)($event);
    }
}
