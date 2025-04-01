<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentReplyController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<UpdateCommentReplyController>
 */
#[CoversClass(UpdateCommentReplyController::class)]
class UpdateCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $replyRepository;
    private TranslatorInterface&MockObject    $translator;
    private MessageBusInterface&MockObject    $bus;

    public function setUp(): void
    {
        $this->replyRepository = $this->createMock(CommentReplyRepository::class);
        $this->translator      = $this->createMock(TranslatorInterface::class);
        $this->bus             = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testInvokeIsNotSubmitted(): void
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
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $reply);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

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

        $response = ($this->controller)($request, $reply);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $reply->getUpdateTimestamp(), 10);
    }

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

        $response = ($this->controller)($request, $reply);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $reply->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentReplyController($this->replyRepository, $this->translator, $this->bus);
    }
}
