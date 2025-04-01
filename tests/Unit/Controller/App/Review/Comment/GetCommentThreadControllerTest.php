<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\GetCommentThreadController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Request\Comment\GetCommentThreadRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModelProvider\CommentViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<GetCommentThreadController>
 */
#[CoversClass(GetCommentThreadController::class)]
class GetCommentThreadControllerTest extends AbstractControllerTestCase
{
    private CommentViewModelProvider&MockObject $modelProvider;

    public function setUp(): void
    {
        $this->modelProvider = $this->createMock(CommentViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request = $this->createMock(GetCommentThreadRequest::class);
        $request->method('getAction')->willReturn(null);

        $review = new CodeReview();

        $comment = new Comment();
        $comment->setId(123);
        $comment->setReview($review);

        $result = ($this->controller)($request, $comment);
        static::assertSame(['comment' => $comment, 'visible' => true, 'review' => $review], $result);
    }

    public function testInvokeEditComment(): void
    {
        $comment = new Comment();
        $comment->setId(123);
        $comment->setReview(new CodeReview());

        $action = new EditCommentAction($comment);

        $request = $this->createMock(GetCommentThreadRequest::class);
        $request->method('getAction')->willReturn($action);

        $this->modelProvider->expects(self::once())->method('getEditCommentViewModel')->with($action);

        $result = ($this->controller)($request, $comment);
        static::assertSame($comment, $result['comment']);
    }

    public function testInvokeAddCommentReply(): void
    {
        $comment = new Comment();
        $comment->setId(123);
        $comment->setReview(new CodeReview());

        $action = new AddCommentReplyAction($comment);

        $request = $this->createMock(GetCommentThreadRequest::class);
        $request->method('getAction')->willReturn($action);

        $this->modelProvider->expects(self::once())->method('getReplyCommentViewModel')->with($action);

        $result = ($this->controller)($request, $comment);
        static::assertSame($comment, $result['comment']);
    }

    public function testInvokeEditCommentReply(): void
    {
        $comment = new Comment();
        $comment->setId(123);
        $comment->setReview(new CodeReview());

        $action = new EditCommentReplyAction(new CommentReply());

        $request = $this->createMock(GetCommentThreadRequest::class);
        $request->method('getAction')->willReturn($action);

        $this->modelProvider->expects(self::once())->method('getEditCommentReplyViewModel')->with($action);

        $result = ($this->controller)($request, $comment);
        static::assertSame($comment, $result['comment']);
    }

    public function getController(): AbstractController
    {
        return new GetCommentThreadController($this->modelProvider);
    }
}
