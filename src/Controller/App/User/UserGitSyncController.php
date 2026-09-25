<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\User\UserGitSyncViewModel;
use Exception;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitSyncController extends AbstractController
{
    public function __construct(private readonly bool $gitlabCommentSyncEnabled, private readonly bool $gitlabReviewerSyncEnabled)
    {
    }

    /**
     * @return array<string, UserGitSyncViewModel>
     * @throws Exception
     */
    #[Route('/app/user/git-sync', self::class, methods: 'GET')]
    #[Template('app/user/user.git-sync.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): array
    {
        $user  = $this->getUser();
        $token = $user->getGitAccessTokens()->findFirst(static fn($key, $token) => $token->getGitType() === RepositoryGitType::GITLAB);

        return ['gitSyncModel' => new UserGitSyncViewModel($this->gitlabCommentSyncEnabled || $this->gitlabReviewerSyncEnabled, $token !== null)];
    }
}
