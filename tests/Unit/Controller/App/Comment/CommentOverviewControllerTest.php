<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Comment\CommentOverviewController;
use DR\Review\Entity\User\User;
use DR\Review\Request\Comment\CommentOverviewRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Comment\CommentOverviewViewModel;
use DR\Review\ViewModelProvider\CommentOverviewViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<CommentOverviewController>
 */
#[CoversClass(CommentOverviewController::class)]
class CommentOverviewControllerTest extends AbstractControllerTestCase
{
    private CommentOverviewViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->viewModelProvider = $this->createMock(CommentOverviewViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user      = new User();
        $request   = static::createStub(CommentOverviewRequest::class);
        $viewModel = static::createStub(CommentOverviewViewModel::class);

        $this->expectGetUser($user);
        $this->viewModelProvider
            ->expects($this->once())
            ->method('getCommentOverviewViewModel')
            ->with($user, $request)
            ->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame('comments.overview', $result['page_title']);
        static::assertSame($viewModel, $result['viewModel']);
    }

    public function getController(): AbstractController
    {
        return new CommentOverviewController($this->viewModelProvider);
    }
}
