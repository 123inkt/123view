<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Entity\User\User;
use DR\Review\Form\User\RegistrationFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher, private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return array<string, object>|Response
     * @throws Exception
     */
    #[Route('/register', name: self::class, condition: 'env("bool:APP_AUTH_PASSWORD")')]
    #[Template('authentication/register.html.twig')]
    public function register(Request $request): array|Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            // make first user admin
            if ($this->userRepository->getUserCount() === 0) {
                $user->setRoles([Roles::ROLE_USER, Roles::ROLE_ADMIN]);
            }

            // save user
            $this->userRepository->save($user, true);

            return $this->redirectToRoute(LoginController::class);
        }

        return ['registrationForm' => $form->createView()];
    }
}
