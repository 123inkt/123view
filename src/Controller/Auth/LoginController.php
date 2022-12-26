<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Form\User\LoginFormType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    /**
     * @return array<string, string|object>
     */
    #[Route('login', name: self::class)]
    #[Template('authentication/login.html.twig')]
    public function __invoke(AuthenticationUtils $authenticationUtils): array
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class, null, ['username' => $lastUsername])->createView();

        return [
            'form'          => $form,
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
    }
}
