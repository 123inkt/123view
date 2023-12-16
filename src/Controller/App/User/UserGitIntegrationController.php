<?php

declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Form\User\GitlabAccessTokenFormType;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\User\GitIntegrationViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitIntegrationController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @return array<string, GitIntegrationViewModel>|Response
     * @throws Exception
     */
    #[Route('/app/user/git-integration', self::class, methods: ['GET', 'POST'])]
    #[Template('app/user/user.git-integration.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array|Response
    {
        $user = $this->getUser();
        $form = $this->createForm(GitlabAccessTokenFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['gitIntegrationViewModel' => new GitIntegrationViewModel($form->createView())];
        }

        $this->addFlash('success', 'access.token.saved.success');

        $this->userRepository->save($user);

        return $this->redirectToRoute(UserGitIntegrationController::class);
    }
}
