<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class Branches
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @return array{
     *     name: string,
     *     merged: bool,
     *     protected: bool,
     *     web_url: string
     * }|null
     * @throws Throwable
     */
    public function getBranch(int $projectId, string $remoteRef): ?array
    {
        $response = $this->client->request(
            'GET',
            sprintf('projects/%d/repository/branches/%s', $projectId, $remoteRef)
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return null;
        }

        return $response->toArray();
    }
}
