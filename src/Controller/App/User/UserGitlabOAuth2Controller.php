<?php

declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitlabOAuth2Controller extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/app/user/gitlab-auth-finalize ', self::class, methods: ['GET', 'POST'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): Response
    {
        return new Response(print_r($request->query->all(), true). ' ' . print_r($request->request->all(), true));
    }
}
