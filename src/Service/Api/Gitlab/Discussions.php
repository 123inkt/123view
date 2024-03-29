<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Position;
use DR\Utils\Arrays;
use Generator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class Discussions implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @phpstan-return Generator<array{
     *    id: string,
     *    notes: array<array{
     *      id: int,
     *      body: string,
     *      position: array{
     *        base_sha: string,
     *        start_sha: string,
     *        head_sha: string,
     *        old_path: string,
     *        new_path: string,
     *      }
     *    }>
     * }>
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#merge-requests
     * @link https://docs.gitlab.com/ee/api/rest/index.html#pagination-link-header
     */
    public function getDiscussions(int $projectId, int $mergeRequestIId, int $perPage = 20): Generator
    {
        $page = 1;
        do {
            $response = $this->client->request(
                'GET',
                sprintf('projects/%d/merge_requests/%d/discussions', $projectId, $mergeRequestIId),
                ['query' => ['per_page' => $perPage, 'page' => $page]]
            );
            $page     = (int)($response->getHeaders()['x-next-page'][0] ?? -1);
            yield from $response->toArray();
        } while ($page > 0);
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#create-a-new-thread-in-the-merge-request-diff
     */
    public function createDiscussion(int $projectId, int $mergeRequestIId, Position $position, string $body): string
    {
        $postBody = [
            'position[position_type]' => $position->positionType,
            'position[base_sha]'      => $position->baseSha,
            'position[head_sha]'      => $position->headSha,
            'position[start_sha]'     => $position->startSha,
            'position[old_path]'      => $position->oldPath,
            'position[new_path]'      => $position->newPath,
            'position[old_line]'      => $position->oldLine,
            'position[new_line]'      => $position->newLine,
            'body'                    => $body
        ];

        $response = $this->client->request(
            'POST',
            sprintf('projects/%d/merge_requests/%d/discussions', $projectId, $mergeRequestIId),
            ['body' => Arrays::removeNull($postBody)]
        );

        $data = $this->responseToJson($response);
        $discussionId = (string)$data['id'];
        $noteId = (string)$data['notes'][0]['id'];

        return sprintf('%s:%s:%s', $mergeRequestIId, $discussionId, $noteId);
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#resolve-a-merge-request-thread
     */
    public function resolve(int $projectId, int $mergeRequestIId, string $discussionId, bool $resolve = true): void
    {
        $this->client->request(
            'PUT',
            sprintf('projects/%d/merge_requests/%d/discussions/%s', $projectId, $mergeRequestIId, $discussionId),
            ['query' => ['resolved' => $resolve ? 'true' : 'false']]
        );
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#add-note-to-existing-merge-request-thread
     */
    public function createNote(int $projectId, int $mergeRequestIId, string $discussionId, string $message): string
    {
        $response = $this->client->request(
            'POST',
            sprintf('projects/%d/merge_requests/%d/discussions/%s/notes', $projectId, $mergeRequestIId, $discussionId),
            ['query' => ['body' => $message]]
        );
        $data     = $this->responseToJson($response);

        return sprintf('%s:%s:%s', $mergeRequestIId, $discussionId, $data['id']);
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#modify-an-existing-merge-request-thread-note
     */
    public function updateNote(int $projectId, int $mergeRequestIId, string $discussionId, string $noteId, string $body): void
    {
        $this->client->request(
            'PUT',
            sprintf('projects/%d/merge_requests/%d/discussions/%s/notes/%s', $projectId, $mergeRequestIId, $discussionId, $noteId),
            ['query' => ['body' => $body]]
        );
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#delete-a-merge-request-thread-note
     */
    public function deleteNote(int $projectId, int $mergeRequestIId, string $discussionId, string $noteId): void
    {
        $this->client->request(
            'DELETE',
            sprintf('projects/%d/merge_requests/%d/discussions/%s/notes/%s', $projectId, $mergeRequestIId, $discussionId, $noteId)
        );
    }

    /**
     * @codeCoverageIgnore
     * @throws Throwable
     */
    private function responseToJson(ResponseInterface $response): array // phpcs:ignore
    {
        try {
            return $response->toArray();
        } catch (Throwable $exception) {
            $this->logger?->warning('Gitlab discussion api failure: {data}', ['data' => $response->getContent(false)]);
            throw $exception;
        }
    }
}
