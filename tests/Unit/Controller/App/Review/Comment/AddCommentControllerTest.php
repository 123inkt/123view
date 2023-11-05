<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentController;
use DR\Review\Controller\App\Review\Comment\GetCommentThreadController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\AddCommentController
 * @covers ::__construct
 */
class AddCommentControllerTest extends AbstractControllerTestCase
{
    private CommentRepository&MockObject          $commentRepository;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private MessageBusInterface&MockObject        $bus;

    public function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->messageFactory    = $this->createMock(CommentEventMessageFactory::class);
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

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     * @covers ::refererRedirect
     */
    public function testInvokeFormSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);
        $data  = ['lineReference' => 'filepath:1:2:3', 'message' => 'my-comment'];
        $event = new CommentAdded(1, 2, 3, 'file', 'message');

        $user = new User();
        $this->expectGetUser($user);

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(true)
            ->isValidWillReturn(true)
            ->getDataWillReturn($data);

        $this->commentRepository->expects(self::once())
            ->method('save')
            ->with(
                self::callback(static function (Comment $comment) use ($user, $review) {
                    $comment->setId(123);
                    static::assertSame($user, $comment->getUser());
                    static::assertSame($review, $comment->getReview());
                    static::assertSame('filepath', $comment->getFilePath());
                    static::assertSame('my-comment', $comment->getMessage());
                    static::assertEquals(LineReference::fromString('filepath:1:2:3'), $comment->getLineReference());
                    static::assertGreaterThan(0, $comment->getCreateTimestamp());
                    static::assertGreaterThan(0, $comment->getUpdateTimestamp());

                    return true;
                }),
                true
            );
        $this->messageFactory->expects(self::once())->method('createAdded')->willReturn($event);
        $this->bus->expects(self::once())->method('dispatch')->with($event)->willReturn($this->envelope);
        $this->expectGenerateUrl(GetCommentThreadController::class, ['id' => 123]);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new AddCommentController($this->commentRepository, $this->messageFactory, $this->bus);
    }
}
