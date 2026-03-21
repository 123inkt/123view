<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModelProvider\CommentViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;

#[CoversClass(CommentViewModelProvider::class)]
class CommentViewModelProviderTest extends AbstractTestCase
{
    private FormFactoryInterface&MockObject $formFactory;
    private CommentViewModelProvider        $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->provider    = new CommentViewModelProvider($this->formFactory);
    }

    public function testGetEditCommentViewModelNullCommentShouldReturnNull(): void
    {
        $this->formFactory->expects($this->never())->method('create');
        $action = new EditCommentAction(null);
        static::assertNull($this->provider->getEditCommentViewModel($action));
    }

    public function testGetEditCommentViewModel(): void
    {
        $comment = new Comment();
        $action  = new EditCommentAction($comment);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(EditCommentFormType::class, $comment, ['comment' => $comment]);

        $viewModel = $this->provider->getEditCommentViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($comment, $viewModel->comment);
    }

    public function testGetReplyCommentViewModelNullCommentShouldReturnNull(): void
    {
        $this->formFactory->expects($this->never())->method('create');
        $action = new AddCommentReplyAction(null);
        static::assertNull($this->provider->getReplyCommentViewModel($action));
    }

    public function testGetReplyCommentViewModel(): void
    {
        $comment = new Comment();
        $action  = new AddCommentReplyAction($comment);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(AddCommentReplyFormType::class, null, ['comment' => $comment]);

        $viewModel = $this->provider->getReplyCommentViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($comment, $viewModel->comment);
    }

    public function testGetEditCommentReplyViewModelNullCommentShouldReturnNull(): void
    {
        $this->formFactory->expects($this->never())->method('create');
        $action = new EditCommentReplyAction(null);
        static::assertNull($this->provider->getEditCommentReplyViewModel($action));
    }

    public function testGetEditCommentReplyViewModel(): void
    {
        $reply  = new CommentReply();
        $action = new EditCommentReplyAction($reply);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(EditCommentReplyFormType::class, $reply, ['reply' => $reply]);

        $viewModel = $this->provider->getEditCommentReplyViewModel($action);
        static::assertNotNull($viewModel);
        static::assertSame($reply, $viewModel->reply);
    }
}
