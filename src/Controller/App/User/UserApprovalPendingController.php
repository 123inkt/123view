<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserApprovalPendingController extends AbstractController
{
    public function __construct(private readonly AuthorizationCheckerInterface $security)
    {
    }

    /**
     * @return string[]|RedirectResponse
     */
    #[Route('/app/user-approval-pending', self::class, methods: ['GET'])]
    #[Template('app/user/user.approval.pending.html.twig')]
    public function __invoke(): array|RedirectResponse
    {
        if ($this->security->isGranted(Roles::ROLE_USER)) {
            return $this->redirectToRoute(ProjectsController::class);
        }

        return [];
    }
}
