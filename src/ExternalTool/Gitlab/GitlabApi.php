<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ExternalTool\Gitlab;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GitlabApi
{
    private HttpClientInterface $client;
    private CacheInterface      $cache;

    public function __construct(HttpClientInterface $gitlabClient, CacheInterface $gitlabCache)
    {
        $this->client = $gitlabClient;
        $this->cache  = $gitlabCache;
    }

    /**
     * @throws Throwable
     */
    public function getBranchUrl(int $projectId, string $remoteRef): ?string
    {
        return $this->cache->get(
            sprintf("branch-url-%s-%s", $projectId, $remoteRef),
            function () use ($projectId, $remoteRef) {
                $response = $this->client->request(
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
        return $this->cache->get(
            sprintf("merge-request-url-%s-%s", $projectId, $remoteRef),
            function () use ($projectId, $remoteRef) {
                $response = $this->client->request(
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
