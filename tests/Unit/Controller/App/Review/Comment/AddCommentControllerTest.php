<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\AddCommentController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\AddCommentController
 * @covers ::__construct
 */
class AddCommentControllerTest extends AbstractControllerTestCase
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
     * @covers ::refererRedirect
     */
    public function testInvokeFormNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(RedirectResponse::class, $response);
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
        $data = ['lineReference' => 'filepath:1:2:3', 'message' => 'my-comment'];
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
                    static::assertSame($user, $comment->getUser());
                    static::assertSame($review, $comment->getReview());
                    static::assertSame('filepath', $comment->getFilePath());
                    static::assertSame('my-comment', $comment->getMessage());
                    static::assertEquals(LineReference::fromString('filepath:1:2:3'), $comment->getLineReference());
                    static::assertNotNull($comment->getCreateTimestamp());
                    static::assertNotNull($comment->getUpdateTimestamp());

                    return true;
                }),
                true
            );
        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);

        $this->bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(CommentAdded::class))->willReturn($this->envelope);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    public function getController(): AbstractController
    {
        return new AddCommentController($this->commentRepository, $this->bus);
    }
}
