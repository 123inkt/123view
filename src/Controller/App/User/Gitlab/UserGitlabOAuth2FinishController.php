<?php

declare(strict_types=1);

namespace DR\Review\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\UserGitSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Repository\User\GitAccessTokenRepository;
use DR\Review\Security\Role\Roles;
use Exception;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserGitlabOAuth2FinishController extends AbstractController
{
    public function __construct(private readonly OAuth2Provider $gitlabOAuth2Provider, private readonly GitAccessTokenRepository $tokenRepository)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/app/user/gitlab-auth-finalize', self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): Response
    {
        if ($request->query->has('code') === false) {
            throw new BadRequestHttpException('Missing `code` query parameter');
        }

        $sessionState = $request->getSession()->get('gitlab.oauth2.state');
        $sessionPkce  = $request->getSession()->get('gitlab.oauth2.pkce');
        $request->getSession()->remove('gitlab.oauth2.state');
        $request->getSession()->remove('gitlab.oauth2.pkce');
        if (is_string($sessionState) === false || is_string($sessionPkce) === false || $request->query->get('state') !== $sessionState) {
            throw new BadRequestHttpException('Invalid state');
        }

        // Try to get an access token using the authorization code grant.
        $this->gitlabOAuth2Provider->setPkceCode($sessionPkce);
        $accessToken = $this->gitlabOAuth2Provider->getAccessToken('authorization_code', ['code' => $request->query->get('code')]);

        $user  = $this->getUser();
        $token = $user->getGitAccessTokens()->findFirst(static fn($key, $token) => $token->getGitType() === RepositoryGitType::GITLAB);

        // create new if none exists
        if ($token === null) {
            $token = new GitAccessToken();
            $token->setUser($user);
            $token->setGitType(RepositoryGitType::GITLAB);
            $user->getGitAccessTokens()->add($token);
        }

        $token->setToken(Json::encode($accessToken));
        $this->tokenRepository->save($token, true);

        $this->addFlash('success', 'gitlab.comment.sync.enabled');

        return $this->redirectToRoute(UserGitSyncController::class);
    }
}
