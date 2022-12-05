<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class UserApprovalPendingController
{
    /**
     * @return string[]
     */
    #[Route('/app/user-approval-pending', self::class, methods: ['GET'])]
    #[Template('app/user/user.approval.pending.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
