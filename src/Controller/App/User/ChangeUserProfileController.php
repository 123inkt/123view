<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\User\User;
use DR\Review\Form\User\UserProfileFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChangeUserProfileController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('/app/users/{id<\d+>}/profile', self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    #[Entity('user')]
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $form = $this->createForm(UserProfileFormType::class, $user, ['user' => $user]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(UsersController::class);
        }

        $this->userRepository->save($user, true);

        $this->addFlash('success', 'user.profile.saved.successful');

        return $this->refererRedirect(UsersController::class);
    }
}
