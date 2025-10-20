<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class ReviewMergeRequestService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @throws Throwable
     */
    public function retrieveMergeRequestIID(GitlabApi $api, CodeReview $review): ?int
    {
        if ($review->getExtReferenceId() !== null) {
            return (int)$review->getExtReferenceId();
        }

        if ($review->getType() === CodeReviewType::BRANCH) {
            $remoteRef = str_replace("origin/", "", $review->getReferenceId());
        } else {
            $remoteRef = $review->getRevisions()->findFirst(static fn($key, Revision $value) => $value->getFirstBranch() !== null)?->getFirstBranch();
        }
        if ($remoteRef === null) {
            $this->logger?->info('No branch name found for review {id}', ['id' => $review->getId()]);

            return null;
        }

        $projectId = (int)$review->getRepository()->getRepositoryProperty('gitlab-project-id');
        $mergeRequest = $api->mergeRequests()->findByRemoteRef($projectId, $remoteRef);
        if ($mergeRequest === null) {
            $this->logger?->info('No merge request found for review {id} - {ref}', ['id' => $review->getId(), 'ref' => $remoteRef]);

            return null;
        }

        $this->logger?->info(
            'Match review {id} to merge request - {projectId} {iid}',
            ['id' => $review->getId(), 'projectId' => $projectId, 'iid' => $mergeRequest['iid']]
        );

        $review->setExtReferenceId((string)$mergeRequest['iid']);
        $this->reviewRepository->save($review, true);

        return (int)$review->getExtReferenceId();
    }
}
