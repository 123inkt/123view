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

class GitlabApiProvider
{
    public function __construct(
        private readonly string $token,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function create(Repository $repository, User $user): ?GitlabApi
    {
        if ($repository->getGitType() !== RepositoryGitType::GITLAB) {
            return null;
        }

        // todo get token from user

        $gitlabHost = Assert::notNull($repository->getUrl()->getHost());
        $httpClient = $this->httpClient
            ->withOptions(
                [
                    'base_uri'      => 'https://' . $gitlabHost . '/api/v4/',
                    'max_redirects' => 0,
                    'headers'       => ['PRIVATE-TOKEN' => $this->token]
                ]
            );

        return new GitlabApi($this->logger, $httpClient, $this->serializer);
    }
}
