<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Repository\User\GitAccessTokenRepository;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use Nette\Utils\Json;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class OAuth2Authenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly OAuth2Provider $gitlabOAuth2Provider, private readonly GitAccessTokenRepository $tokenRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function getAuthorizationHeader(GitAccessToken $gitToken): string
    {
        $accessToken = new AccessToken(Json::decode($gitToken->getToken(), true));

        if ($accessToken->hasExpired()) {
            $this->logger?->info('Gitlab access token expired, refreshing token for {user}', ['user' => $gitToken->getUser()->getEmail()]);
            $accessToken = $this->gitlabOAuth2Provider->getAccessToken(new RefreshToken(), ['refresh_token' => $accessToken->getRefreshToken()]);
            $gitToken->setToken(Json::encode($accessToken->jsonSerialize()));
            $this->tokenRepository->save($gitToken);
        }

        $this->logger?->debug('Authenticating request with gitlab access token for {user}', ['user' => $gitToken->getUser()->getEmail()]);

        return 'Bearer ' . $accessToken->getToken();
    }
}
