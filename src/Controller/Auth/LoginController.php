<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('login', name: self::class)]
    #[Template('authentication/login.html.twig')]
    public function __invoke(AuthenticationUtils $authenticationUtils): array
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
    }
}
