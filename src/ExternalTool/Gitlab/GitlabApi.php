<?php
declare(strict_types=1);

namespace DR\Review\ExternalTool\Gitlab;

use DR\Review\Model\Webhook\Gitlab\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GitlabApi
{
    public function __construct(
        private readonly HttpClientInterface $gitlabClient,
        private readonly CacheInterface $gitlabCache,
        private readonly SerializerInterface $objectSerializer
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getUser(int $userId): User
    {
        return $this->gitlabCache->get(
            sprintf("user-id-%d", $userId),
            function () use ($userId) {
                $data = $this->gitlabClient->request('GET', sprintf('users/%d', $userId))->getContent();

                return $this->objectSerializer->deserialize($data, User::class, JsonEncoder::FORMAT);
            }
        );
    }

    /**
     * @throws Throwable
     */
    public function getBranchUrl(int $projectId, string $remoteRef): ?string
    {
        return $this->gitlabCache->get(
            sprintf("branch-url-%s-%s", $projectId, $remoteRef),
            function () use ($projectId, $remoteRef) {
                $response = $this->gitlabClient->request(
                    'GET',
                    sprintf('projects/%d/repository/branches/%s', $projectId, $remoteRef)
                )->toArray(false);

                return $response['web_url'] ?? null;
            }
        );
    }

    /**
     * @throws Throwable
     */
    public function getMergeRequestUrl(int $projectId, string $remoteRef): ?string
    {
        return $this->gitlabCache->get(
            sprintf("merge-request-url-%s-%s", $projectId, $remoteRef),
            function () use ($projectId, $remoteRef) {
                $response = $this->gitlabClient->request(
                    'GET',
                    sprintf('projects/%d/merge_requests', $projectId),
                    [
                        'query' => [
                            'scope'         => 'all',
                            'per_page'      => 1,
                            'source_branch' => $remoteRef
                        ]
                    ]
                )->toArray(false);

                return $response[0]['web_url'] ?? null;
            }
        );
    }
}
