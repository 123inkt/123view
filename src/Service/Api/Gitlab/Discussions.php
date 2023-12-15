<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Position;
use DR\Utils\Arrays;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class Discussions
{
    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#create-a-new-thread-in-the-merge-request-diff
     */
    public function create(int $projectId, int $mergeRequestIId, Position $position, string $body): string
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
        )->toArray();

        // TODO
        return (string)$response['id'];
    }

    /**
     * @throws Throwable
     */
    public function update(int $projectId, int $mergeRequestIId, int $discussionId, string $body): void
    {
        $this->client->request(
            'PUT',
            sprintf('projects/%d/merge_requests/%d/discussions/%d', $projectId, $mergeRequestIId, $discussionId),
            ['query' => ['body' => $body]]
        );
    }

    /**
     * @throws Throwable
     * @link https://docs.gitlab.com/ee/api/discussions.html#resolve-a-merge-request-thread
     */
    public function resolve(int $projectId, int $mergeRequestIId, int $discussionId, bool $resolve = true): void
    {
        $this->client->request(
            'PUT',
            sprintf('projects/%d/merge_requests/%d/discussions/%d', $projectId, $mergeRequestIId, $discussionId),
            ['query' => ['resolved' => $resolve ? 'true' : 'false']]
        );
    }
}
