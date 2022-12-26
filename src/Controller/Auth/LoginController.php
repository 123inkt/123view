<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\User\UserApprovalPendingController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\Authentication\LoginViewModel;
use DR\Review\ViewModelProvider\LoginViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly LoginViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @return array<string, LoginViewModel>|Response
     */
    #[Route('/', name: self::class)]
    #[Template('authentication/login.html.twig')]
    public function __invoke(Request $request): array|Response
    {
        if ($this->security->getUser() !== null) {
            if (in_array(Roles::ROLE_USER, $this->security->getUser()->getRoles(), true) === false) {
                return $this->redirectToRoute(UserApprovalPendingController::class);
            }

            return $this->redirectToRoute(ProjectsController::class);
        }

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        if ($error !== null) {
            $this->addFlash('error', $this->translator->trans($error->getMessageKey(), $error->getMessageData(), 'security'));
        }

        return [
            'page_title' => $this->translator->trans('page.title.single.sign.on'),
            'loginModel' => $this->viewModelProvider->getLoginViewModel($request)
        ];
    }
}
