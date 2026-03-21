<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserMandatoryGitlabSyncController extends AbstractController
{
    #[Route('/app/user-gitlab-sync-mandatory', self::class, methods: ['GET'])]
    #[Template('app/user/user.gitlab.sync.mandatory.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(): void
    {
        // no special logic required, template only
    }
}
