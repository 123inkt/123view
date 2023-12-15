<?php

declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class MergeRequests
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @return array{
     *     id: int,
     *     iid: int,
     *     title: string,
     *     web_url: string
     * }|null
     * @throws Throwable
     */
    public function findByRemoteRef(int $projectId, string $remoteRef): ?array
    {
        $result = $this->client->request(
            'GET',
            sprintf('projects/%d/merge_requests', $projectId),
            [
                'query' => [
                    'scope'         => 'all',
                    'per_page'      => 1,
                    'source_branch' => $remoteRef
                ]
            ]
        )->toArray();

        return count($result) === 0 ? null : $result[0];
    }
}
