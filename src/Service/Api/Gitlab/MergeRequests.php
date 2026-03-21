<?php

declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Version;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class MergeRequests
{
    public function __construct(private readonly HttpClientInterface $client, private readonly SerializerInterface $serializer)
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

    /**
     * @return Version[]
     * @throws Throwable
     */
    public function versions(int $projectId, int $mergeRequestIId): array
    {
        $json = $this->client->request('GET', sprintf('projects/%d/merge_requests/%d/versions', $projectId, $mergeRequestIId))->getContent();

        /** @phpstan-var Version[] */
        return $this->serializer->deserialize(
            $json,
            Version::class . '[]',
            JsonEncoder::FORMAT,
            [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]
        );
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function approve(int $projectId, int $mergeRequestIId): bool
    {
        $response = $this->client->request('POST', sprintf('projects/%d/merge_requests/%d/approve', $projectId, $mergeRequestIId));

        return $response->getStatusCode() === Response::HTTP_CREATED;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function unapprove(int $projectId, int $mergeRequestIId): bool
    {
        $response = $this->client->request('POST', sprintf('projects/%d/merge_requests/%d/unapprove', $projectId, $mergeRequestIId));

        return $response->getStatusCode() === Response::HTTP_CREATED;
    }
}
