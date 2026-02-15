<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use PHPUnit\Framework\MockObject\Stub;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentReplyController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<AddCommentReplyController>
 */
#[CoversClass(AddCommentReplyController::class)]
class AddCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;
    private TranslatorInterface&Stub    $translator;
    private MessageBusInterface&MockObject    $bus;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
        $this->translator        = static::createStub(TranslatorInterface::class);
        $this->bus               = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeCommentMissing(): void
    {
        $this->commentRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');
        $response = ($this->controller)(new Request(), null);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testInvokeFormNotSubmitted(): void
    {
        $this->commentRepository->expects($this->never())->method('save');
        $this->bus->expects($this->never())->method('dispatch');
        $user    = (new User())->setId(789);
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setReview($review);

        $this->expectGetUser($user);
        $this->expectCreateForm(AddCommentReplyFormType::class, static::isInstanceOf(CommentReply::class), ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testInvokeFormSubmitted(): void
    {
        $request = new Request();
        $review  = (new CodeReview())->setId(123);
        $comment = (new Comment())->setId(456)->setFilePath('file')->setReview($review);
        $user    = (new User())->setId(789);
        $this->expectGetUser($user);

        $this->expectCreateForm(AddCommentReplyFormType::class, static::isInstanceOf(CommentReply::class), ['comment' => $comment])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true);

        $this->commentRepository->expects($this->once())
            ->method('save')
            ->with(
                self::callback(static function (CommentReply $reply) use ($user, $comment) {
                    static::assertSame($user, $reply->getUser());
                    static::assertSame($comment, $reply->getComment());
                    static::assertGreaterThan(0, $reply->getCreateTimestamp());
                    static::assertGreaterThan(0, $reply->getUpdateTimestamp());

                    return true;
                }),
                true
            );

        $this->bus->expects($this->once())->method('dispatch')->with(self::isInstanceOf(CommentReplyAdded::class))->willReturn($this->envelope);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new AddCommentReplyController($this->commentRepository, $this->translator, $this->bus);
    }
}
