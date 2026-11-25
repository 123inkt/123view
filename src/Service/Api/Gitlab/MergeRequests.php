<?php

declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Note;
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
     * @return Note[]
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/notes.html#list-all-merge-request-notes
     */
    public function notes(int $projectId, int $mergeRequestIId, int $perPage = 100): array
    {
        $notes = [];
        $page  = 1;
        do {
            $response = $this->client->request(
                'GET',
                sprintf('projects/%d/merge_requests/%d/notes', $projectId, $mergeRequestIId),
                ['query' => ['sort' => 'desc', 'order_by' => 'created_at', 'per_page' => $perPage, 'page' => $page]]
            );
            $page     = (int)($response->getHeaders()['x-next-page'][0] ?? -1);
            /** @phpstan-var list<Note> $pageNotes */
            $pageNotes = $this->serializer->deserialize(
                $response->getContent(),
                Note::class . '[]',
                JsonEncoder::FORMAT,
                [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]
            );
            $notes     = [...$notes, ...$pageNotes];
        } while ($page > 0);

        return $notes;
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
