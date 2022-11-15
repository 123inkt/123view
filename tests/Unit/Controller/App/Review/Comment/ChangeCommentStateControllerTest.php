<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\ChangeCommentStateController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Request\Comment\ChangeCommentStateRequest;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\ChangeCommentStateController
 * @covers ::__construct
 */
class ChangeCommentStateControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject   $commentRepository;
    private MessageBusInterface&MockObject $bus;
    private Envelope                       $envelope;

    public function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     * @covers ::getUser
     */
    public function testInvoke(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CommentStateType::RESOLVED);

        $review = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setState(CommentStateType::OPEN);
        $comment->setReview($review);

        $user = new User();
        $user->setId(789);

        $this->expectGetUser($user);
        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->bus->expects(self::once())->method('dispatch')->with(new CommentResolved(123, 456, 789))->willReturn($this->envelope);
        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        ($this->controller)($request, $comment);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeShouldNotDispatchIfStateDidNotChange(): void
    {
        $request = $this->createMock(ChangeCommentStateRequest::class);
        $request->expects(self::once())->method('getState')->willReturn(CommentStateType::OPEN);

        $review = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setState(CommentStateType::OPEN);
        $comment->setReview($review);

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->bus->expects(self::never())->method('dispatch');
        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        ($this->controller)($request, $comment);
    }

    public function getController(): AbstractController
    {
        return new ChangeCommentStateController($this->commentRepository, $this->bus);
    }
}
