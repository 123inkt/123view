<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Form\User\UserProfileType;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(UsersController::class);
        }

        $this->userRepository->save($user, true);

        $this->addFlash('success', 'user.profile.saved.successful');

        return $this->refererRedirect(UsersController::class);
    }
}
