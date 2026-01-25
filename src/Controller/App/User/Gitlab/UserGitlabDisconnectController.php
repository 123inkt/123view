<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UserGitSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Repository\User\GitAccessTokenRepository;
use DR\Review\Security\Role\Roles;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitlabDisconnectController extends AbstractController
{
    public function __construct(private readonly GitAccessTokenRepository $tokenRepository)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/app/user/gitlab-sync-disconnect', self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): Response
    {
        $token = $this->getUser()->getGitAccessTokens()->findFirst(static fn($key, $token) => $token->getGitType() === RepositoryGitType::GITLAB);
        if ($token !== null) {
            $this->tokenRepository->remove($token, true);
        }

        $this->addFlash('success', 'gitlab.comment.sync.disabled');

        return $this->redirectToRoute(UserGitSyncController::class);
    }
}
