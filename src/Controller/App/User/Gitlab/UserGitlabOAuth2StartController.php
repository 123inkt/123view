<?php

declare(strict_types=1);

namespace DR\Review\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use Exception;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitlabOAuth2StartController extends AbstractController
{
    public function __construct(private readonly OAuth2Provider $gitlabOAuth2Provider)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/app/user/gitlab-auth-start', self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): Response
    {
        $authorizationUrl = $this->gitlabOAuth2Provider->getAuthorizationUrl();
        $request->getSession()->set('gitlab.oauth2.state', $this->gitlabOAuth2Provider->getState());
        $request->getSession()->set('gitlab.oauth2.pkce', $this->gitlabOAuth2Provider->getPkceCode());

        return $this->redirect($authorizationUrl);
    }
}
