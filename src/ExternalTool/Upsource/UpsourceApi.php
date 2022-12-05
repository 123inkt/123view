<?php
declare(strict_types=1);

namespace DR\Review\ExternalTool\Upsource;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class UpsourceApi
{
    private HttpClientInterface $client;
    private CacheInterface      $cache;

    public function __construct(HttpClientInterface $upsourceClient, CacheInterface $upsourceCache)
    {
        $this->client = $upsourceClient;
        $this->cache  = $upsourceCache;
    }

    /**
     * @throws Throwable
     */
    public function getReviewId(string $projectId, string $subject): ?string
    {
        return $this->cache->get(
            sprintf('review-id-%s-%s', $projectId, $subject),
            function () use ($projectId, $subject) {
                $result = $this->client->request(
                    'POST',
                    'getReviews',
                    [
                        'json' => [
                            'projectId' => $projectId,
                            'query'     => '"' . str_replace('"', '\\"', $subject) . '"',
                            'limit'     => 1
                        ]
                    ]
                )->toArray(false);

                return $result['result']['reviews'][0]['reviewId']['reviewId'] ?? null;
            }
        );
    }
}
