<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Security\Role\Roles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class UserAccountSuspendedController
{
    /**
     * @return string[]
     */
    #[Route('/app/user-account-suspended', self::class, methods: ['GET'])]
    #[Template('app/user/user.account.suspended.html.twig')]
    #[IsGranted(Roles::ROLE_BANNED)]
    public function __invoke(): array
    {
        return [];
    }
}
