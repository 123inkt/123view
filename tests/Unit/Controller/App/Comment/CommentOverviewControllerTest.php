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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<CommentOverviewController>
 */
#[CoversClass(CommentOverviewController::class)]
class CommentOverviewControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject             $translator;
    private CommentOverviewViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->viewModelProvider = $this->createMock(CommentOverviewViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user      = new User();
        $request   = static::createStub(CommentOverviewRequest::class);
        $viewModel = static::createStub(CommentOverviewViewModel::class);

        $this->expectGetUser($user);
        $this->translator->expects($this->once())->method('trans')->with('comments.overview')->willReturn('translation');
        $this->viewModelProvider
            ->expects($this->once())
            ->method('getCommentOverviewViewModel')
            ->with($user, $request)
            ->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame('translation', $result['page_title']);
        static::assertSame($viewModel, $result['viewModel']);
    }

    public function getController(): AbstractController
    {
        return new CommentOverviewController($this->translator, $this->viewModelProvider);
    }
}
