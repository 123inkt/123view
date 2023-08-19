<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

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
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\AddCommentReplyController
 * @covers ::__construct
 */
class AddCommentReplyControllerTest extends AbstractControllerTestCase
{
    private CommentReplyRepository&MockObject $commentRepository;
    private TranslatorInterface&MockObject    $translator;
    private MessageBusInterface&MockObject    $bus;
    private Envelope                          $envelope;

    protected function setUp(): void
    {
        $this->envelope          = new Envelope(new stdClass(), []);
        $this->commentRepository = $this->createMock(CommentReplyRepository::class);
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

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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
        $this->expectGetUser($user);

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
                    static::assertGreaterThan(0, $reply->getCreateTimestamp());
                    static::assertGreaterThan(0, $reply->getUpdateTimestamp());

                    return true;
                }),
                true
            );

        $this->bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(CommentReplyAdded::class))->willReturn($this->envelope);

        $response = ($this->controller)($request, $comment);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new AddCommentReplyController($this->commentRepository, $this->translator, $this->bus);
    }
}
