<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class CommentUpdatedMessageHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly bool $gitlabCommentSyncEnabled,
        private readonly string $gitlabApiUrl,
        private readonly string $token,
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewRepository $reviewRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    #[AsMessageHandler(fromTransport: 'sync')]
    public function __invoke(CommentUpdated $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false) {
            return;
        }

        $comment    = Assert::notNull($this->commentRepository->find($event->getCommentId()));
        $review     = $comment->getReview();
        $repository = $review->getRepository();
        if ($repository->getGitType() !== RepositoryGitType::GITLAB || $comment->getExtReferenceId() === null) {
            return;
        }
        $projectId = (int)$repository->getRepositoryProperty('gitlab-project-id');

        $httpClient = $this->httpClient->withOptions(
            ['base_uri' => $this->gitlabApiUrl . 'api/v4/', 'max_redirects' => 0, 'headers' => ['PRIVATE-TOKEN' => $this->token]]
        );
        $api        = new GitlabApi($httpClient, $this->serializer);

        [$mergeRequestIId, $discussionId, $noteId] = explode(':', $comment->getExtReferenceId());

        $this->logger?->info(
            'Updating comment in gitlab: {projectId} {mergeRequestIId} {discussionId}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'discussionId' => $discussionId]
        );
        $api->discussions()->update($projectId, (int)$mergeRequestIId, $discussionId, $noteId, $comment->getMessage());
    }
}
