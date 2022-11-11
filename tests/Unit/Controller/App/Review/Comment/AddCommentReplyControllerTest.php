<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\App\Review\Comment\AddCommentReplyController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\AddCommentReplyController
 * @covers ::__construct
 */
class AddCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;
    private MessageBusInterface&MockObject    $bus;
    private Envelope                          $envelope;

    public function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeFormNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setReview($review);

        $this->expectCreateForm(AddCommentReplyFormType::class, null, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeFormSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setReview($review);
        $data = ['message' => 'my-comment'];
        $user = new User();
        $this->expectUser($user);

        $this->expectCreateForm(AddCommentReplyFormType::class, null, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getDataWillReturn($data);

        $this->commentRepository->expects(self::once())
            ->method('save')
            ->with(
                self::callback(static function (CommentReply $reply) use ($user, $comment) {
                    static::assertSame($user, $reply->getUser());
                    static::assertSame($comment, $reply->getComment());
                    static::assertSame('my-comment', $reply->getMessage());
                    static::assertNotNull($reply->getCreateTimestamp());
                    static::assertNotNull($reply->getUpdateTimestamp());

                    return true;
                }),
                true
            );
        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        $this->bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(CommentReplyAdded::class))->willReturn($this->envelope);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    public function getController(): AbstractController
    {
        return new AddCommentReplyController($this->commentRepository, $this->bus);
    }
}
