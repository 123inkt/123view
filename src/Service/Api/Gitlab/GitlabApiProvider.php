<?php

declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Utils\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GitlabApiProvider
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        private readonly OAuth2Authenticator $authenticator,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function create(Repository $repository, User $user): ?GitlabApi
    {
        if ($repository->getGitType() !== RepositoryGitType::GITLAB) {
            return null;
        }

        // find gitlab access token
        $token = $user->getGitAccessTokens()->findFirst(static fn($key, $token) => $token->getGitType() === RepositoryGitType::GITLAB);
        if ($token === null) {
            $this->logger->info('No gitlab access token found for user {user}', ['user' => $user->getEmail()]);

            return null;
        }

        $gitlabHost = Assert::notNull($repository->getUrl()->getHost());
        $httpClient = $this->httpClient
            ->withOptions(
                [
                    'base_uri'      => 'https://' . $gitlabHost . '/api/v4/',
                    'max_redirects' => 0,
                    'headers'       => ['Authorization' => $this->authenticator->getAuthorizationHeader($token)],
                ]
            );

        return new GitlabApi($this->logger, $httpClient, $this->serializer);
    }
}
