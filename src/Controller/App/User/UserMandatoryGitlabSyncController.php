<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;

class UserMandatoryGitlabSyncController extends AbstractController
{
    #[Route('/app/user-gitlab-sync-mandatory', self::class, methods: ['GET'])]
    #[Template('app/user/user.gitlab.sync.mandatory.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
