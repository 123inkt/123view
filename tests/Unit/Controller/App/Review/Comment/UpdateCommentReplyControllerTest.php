<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentReplyController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\UpdateCommentReplyController
 * @covers ::__construct
 */
class UpdateCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $replyRepository;
    private TranslatorInterface&MockObject    $translator;
    private MessageBusInterface&MockObject    $bus;
    private Envelope                          $envelope;

    public function setUp(): void
    {
        $this->envelope        = new Envelope(new stdClass(), []);
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->translator      = $this->createMock(TranslatorInterface::class);
        $this->bus             = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeCommentMissing(): void
    {
        $this->expectAddFlash('warning', 'comment.was.deleted.meanwhile');
        $this->expectRefererRedirect(ProjectsController::class);

        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setReview($review);
        $reply = new CommentReply();
        $reply->setComment($comment);

        $this->expectDenyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);
        $this->expectCreateForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $reply);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsSubmittedWithoutChanges(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setReview($review);
        $reply = new CommentReply();
        $reply->setMessage('message');
        $reply->setComment($comment);

        $this->expectDenyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);
        $this->expectCreateForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->replyRepository->expects(self::once())->method('save')->with($reply, true);
        $this->bus->expects(self::never())->method('dispatch');

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $reply);

        static::assertEqualsWithDelta(time(), $reply->getUpdateTimestamp(), 10);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeIsSubmittedWithChanges(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setReview($review);
        $reply = new CommentReply();
        $reply->setId(789);
        $reply->setMessage('message');
        $reply->setComment($comment);

        $this->expectGetUser((new User())->setId(101));
        $this->expectDenyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);
        $this->expectCreateForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->replyRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    static function (CommentReply $reply) {
                        $reply->setMessage('changed-message');

                        return true;
                    }
                ),
                true
            );
        $this->bus->expects(self::once())->method('dispatch')->with(new CommentReplyUpdated(123, 789, 101, 'message'))->willReturn($this->envelope);
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        ($this->controller)($request, $reply);

        static::assertEqualsWithDelta(time(), $reply->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentReplyController($this->replyRepository, $this->translator, $this->bus);
    }
}
