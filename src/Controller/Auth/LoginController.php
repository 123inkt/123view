<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Controller\AbstractController;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserEntityProvider $userEntityProvider,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly LoginViewModelProvider $viewModelProvider
    ) {
    }

    #[Route('/', name: self::class)]
    //#[Template('authentication/login.html.twig')]
    public function __invoke(Request $request): LoginViewModel
    {
        return $this->viewModelProvider->getLoginViewModel($request);

        //if ($this->userEntityProvider->getUser() !== null) {
        //    if (in_array(Roles::ROLE_USER, $this->userEntityProvider->getUser()->getRoles(), true) === false) {
        //        return $this->redirectToRoute(UserApprovalPendingController::class);
        //    }
        //
        //    return $this->redirectToRoute(ProjectsController::class);
        //}
        //
        //// get the login error if there is one
        //$error = $this->authenticationUtils->getLastAuthenticationError();
        //if ($error !== null) {
        //    $this->addFlash('error', $this->translator->trans($error->getMessageKey(), $error->getMessageData(), 'security'));
        //}
        //
        //return [
        //    'page_title' => $this->translator->trans('page.title.single.sign.on'),
        //    'loginModel' => $this->viewModelProvider->getLoginViewModel($request)
        //];
    }
}
