<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\UpdateCommentController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\UpdateCommentController
 * @covers ::__construct
 */
class UpdateCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private TranslatorInterface&MockObject        $translator;
    private MessageBusInterface&MockObject        $bus;
    private Envelope                              $envelope;

    public function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeCommentMissing(): void
    {
        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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
        $comment->setMessage('message');
        $comment->setReview($review);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     * @covers ::refererRedirect
     */
    public function testInvokeIsSubmittedWithoutChanges(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setMessage('message');
        $comment->setReview($review);

        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository->expects(self::once())->method('save')->with($comment, true);
        $this->bus->expects(self::never())->method('dispatch');

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
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
        $comment->setMessage('message');
        $comment->setReview($review);
        $event = new CommentUpdated(1, 2, 3, 'file', 'message', 'original');

        $this->expectGetUser((new User())->setId(789));
        $this->expectDenyAccessUnlessGranted(CommentVoter::EDIT, $comment);
        $this->expectCreateForm(EditCommentFormType::class, $comment, ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository
            ->expects(self::once())
            ->method('save')
            ->with(
                self::callback(
                    static function (Comment $comment) {
                        $comment->setMessage('changed-message');

                        return true;
                    }
                ),
                true
            );
        $this->messageFactory->expects(self::once())->method('createUpdated')->willReturn($event);
        $this->bus->expects(self::once())->method('dispatch')->with($event)->willReturn($this->envelope);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());

        static::assertEqualsWithDelta(time(), $comment->getUpdateTimestamp(), 10);
    }

    public function getController(): AbstractController
    {
        return new UpdateCommentController($this->commentRepository, $this->messageFactory, $this->translator, $this->bus);
    }
}
