<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler\Gitlab;

use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class CommentAddedMessageHandler implements LoggerAwareInterface
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
    public function __invoke(CommentAdded $event): void
    {
        if ($this->gitlabCommentSyncEnabled === false) {
            return;
        }

        $comment    = Assert::notNull($this->commentRepository->find($event->getCommentId()));
        $review     = $comment->getReview();
        $repository = $review->getRepository();
        if ($repository->getGitType() !== RepositoryGitType::GITLAB) {
            return;
        }
        $projectId = (int)$repository->getRepositoryProperty('gitlab-project-id');

        $httpClient = $this->httpClient->withOptions(
            ['base_uri' => $this->gitlabApiUrl . 'api/v4/', 'max_redirects' => 0, 'headers' => ['PRIVATE-TOKEN' => $this->token]]
        );
        $api        = new GitlabApi($httpClient, $this->serializer);

        if ($review->getExtReferenceId() === null) {
            $remoteRef = $review->getRevisions()->findFirst(static fn($key, Revision $value) => $value->getFirstBranch() !== null)?->getFirstBranch();
            if ($remoteRef === null) {
                $this->logger?->info('No branch name found for review {id}', ['id' => $review->getId()]);

                return;
            }
            $mergeRequest = $api->mergeRequests()->findByRemoteRef($projectId, $remoteRef);
            if ($mergeRequest === null) {
                $this->logger?->info('No merge request found for review {id} - {ref}', ['id' => $review->getId(), 'ref' => $remoteRef]);

                return;
            }
            $review->setExtReferenceId((string)$mergeRequest['iid']);
            $this->reviewRepository->save($review, true);
        }
        $mergeRequestIId = (int)$review->getExtReferenceId();

        $version = Arrays::firstOrNull($api->mergeRequests()->versions($projectId, $mergeRequestIId));
        if ($version === null) {
            $this->logger?->info('No merge request version found for review {id} - {ref}', ['id' => $review->getId(), 'ref' => $mergeRequestIId]);

            return;
        }

        $lineReference = $comment->getLineReference();

        $position               = new Position();
        $position->positionType = 'text';
        $position->headSha      = $version->headCommitSha;
        $position->startSha     = $version->startCommitSha;
        $position->baseSha      = $version->baseCommitSha;
        $position->oldPath      = $lineReference->oldPath;
        $position->newPath      = $lineReference->newPath;

        if ($lineReference->state === LineReferenceStateEnum::Added || $lineReference->state === LineReferenceStateEnum::Modified) {
            $position->newLine = $lineReference->lineAfter;
        } elseif ($lineReference->state === LineReferenceStateEnum::Deleted) {
            $position->oldLine = $lineReference->line;
        } else {
            $position->oldLine = $lineReference->line;
            $position->newLine = $lineReference->lineAfter;
        }

        $this->logger?->info(
            'Adding comment in gitlab: {projectId} {mergeRequestIId} {comment}',
            ['projectId' => $projectId, 'mergeRequestIId' => $mergeRequestIId, 'comment' => $comment->getMessage()]
        );
        $referenceId = $api->discussions()->create($projectId, $mergeRequestIId, $position, $comment->getMessage());
        $comment->setExtReferenceId($referenceId);
        $this->commentRepository->save($comment, true);
    }
}
