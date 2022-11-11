<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\App\Review\Comment\AddCommentController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\AddCommentController
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
     */
    public function testInvokeFormNotSubmitted(): void
    {
        $request = new Request();
        $review  = new CodeReview();
        $review->setId(123);

        $this->expectCreateForm(AddCommentFormType::class, null, ['review' => $review])
            ->handleRequest($request)
            ->isSubmittedWillReturn(false);

        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        $response = ($this->controller)($request, $review);
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
        $data = ['lineReference' => 'filepath:1:2:3', 'message' => 'my-comment'];
        $user = new User();
        $this->expectUser($user);

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
        $this->expectRefererRedirect(ReviewController::class, ['id' => 123]);

        $this->bus->expects(self::once())->method('dispatch')->with(self::isInstanceOf(CommentAdded::class))->willReturn($this->envelope);

        $response = ($this->controller)($request, $review);
        static::assertInstanceOf(RedirectResponse::class, $response);
    }

    public function getController(): AbstractController
    {
        return new AddCommentController($this->commentRepository, $this->bus);
    }
}
