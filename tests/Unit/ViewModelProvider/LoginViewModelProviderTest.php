<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use DR\Review\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\Review\Form\User\LoginFormType;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[CoversClass(LoginViewModelProvider::class)]
class LoginViewModelProviderTest extends AbstractTestCase
{
    private FormFactoryInterface&MockObject  $formFactory;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private AuthenticationUtils&MockObject   $utils;
    private LoginViewModelProvider           $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->formFactory  = $this->createMock(FormFactoryInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->utils        = $this->createMock(AuthenticationUtils::class);
        $this->provider     = new LoginViewModelProvider($this->formFactory, $this->urlGenerator, $this->utils);
    }

    public function testGetLoginViewModel(): void
    {
        $request = new Request(['next' => 'next']);
        $view    = static::createStub(FormView::class);
        $form    = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('createView')->willReturn($view);

        $this->utils->expects($this->once())->method('getLastUsername')->willReturn('username');

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(LoginFormType::class, null, ['username' => 'username', 'targetPath' => 'next'])
            ->willReturn($form);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(AzureAdAuthController::class, ['next' => 'next'])
            ->willReturn('url');

        $viewModel = $this->provider->getLoginViewModel($request);
        static::assertEquals(new LoginViewModel($view, 'url'), $viewModel);
    }
}
