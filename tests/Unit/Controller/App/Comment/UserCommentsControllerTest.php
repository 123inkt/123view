<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Comment;

use DR\Review\Controller\App\Comment\UserCommentsController;
use DR\Review\Entity\User\User;
use DR\Review\Request\Comment\UserCommentsRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Comment\UserCommentsViewModel;
use DR\Review\ViewModelProvider\UserCommentsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<UserCommentsController>
 */
#[CoversClass(UserCommentsController::class)]
class UserCommentsControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject           $translator;
    private UserCommentsViewModelProvider&MockObject $viewModelProvider;

    protected function setUp(): void
    {
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->viewModelProvider = $this->createMock(UserCommentsViewModelProvider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user      = new User();
        $viewModel = new UserCommentsViewModel([], 'search', 'update-timestamp', 3, 5);

        $request = static::createStub(UserCommentsRequest::class);
        $request->method('getSearchQuery')->willReturn('search');
        $request->method('getOrderBy')->willReturn('update-timestamp');
        $request->method('getPage')->willReturn(3);

        $this->expectGetUser($user);
        $this->translator->expects($this->once())->method('trans')->with('my.comments')->willReturn('translation');
        $this->viewModelProvider
            ->expects($this->once())
            ->method('getUserCommentsViewModel')
            ->with($user, 'search', 'update-timestamp', 3)
            ->willReturn($viewModel);

        $result = ($this->controller)($request);

        static::assertSame(['page_title' => 'translation', 'viewModel' => $viewModel], $result);
    }

    public function getController(): AbstractController
    {
        return new UserCommentsController($this->translator, $this->viewModelProvider);
    }
}
