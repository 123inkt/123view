<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2FinishController;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OAuth2ProviderFactory
{
    public function __construct(
        private string $gitlabApiUrl,
        private string $gitlabApplicationId,
        private string $gitlabApplicationSecret,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function create(): OAuth2Provider
    {
        $redirectUri = $this->urlGenerator->generate(UserGitlabOAuth2FinishController::class, [], UrlGeneratorInterface::ABSOLUTE_URL);

        return new OAuth2Provider(
            [
                'clientId'                => $this->gitlabApplicationId,
                'clientSecret'            => $this->gitlabApplicationSecret,
                'redirectUri'             => $redirectUri,
                'urlAuthorize'            => $this->gitlabApiUrl . 'oauth/authorize',
                'urlAccessToken'          => $this->gitlabApiUrl . 'oauth/token',
                'urlResourceOwnerDetails' => null,
                'pkceMethod'              => OAuth2Provider::PKCE_METHOD_S256
            ]
        );
    }
}
