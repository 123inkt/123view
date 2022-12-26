<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth;

use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\RegistrationFormType;
use DR\Review\Repository\User\UserRepository;
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

            // save user
            $this->userRepository->save($user, true);

            return $this->redirectToRoute(ProjectsController::class);
        }

        return ['registrationForm' => $form->createView()];
    }
}
