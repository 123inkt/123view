<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Comment\DraftCommentController;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Comment\DraftCommentsViewModel;
use DR\Review\ViewModelProvider\DraftCommentViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<DraftCommentController>
 */
#[CoversClass(DraftCommentController::class)]
class DraftCommentControllerTest extends AbstractControllerTestCase
{
    private DraftCommentViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(DraftCommentViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user      = new User();
        $viewModel = $this->createStub(DraftCommentsViewModel::class);
        $request   = new Request(['page' => '3']);

        $this->expectGetUser($user);
        $this->viewModelProvider
            ->expects($this->once())
            ->method('getDraftCommentsViewModel')
            ->with($user, 3)
            ->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame('draft.comments.overview', $result['page_title']);
        static::assertSame($viewModel, $result['viewModel']);
    }

    public function testInvokeDefaultPage(): void
    {
        $user      = new User();
        $viewModel = $this->createStub(DraftCommentsViewModel::class);
        $request   = new Request();

        $this->expectGetUser($user);
        $this->viewModelProvider
            ->expects($this->once())
            ->method('getDraftCommentsViewModel')
            ->with($user, 1)
            ->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame('draft.comments.overview', $result['page_title']);
        static::assertSame($viewModel, $result['viewModel']);
    }

    public function getController(): AbstractController
    {
        return new DraftCommentController($this->viewModelProvider);
    }
}
