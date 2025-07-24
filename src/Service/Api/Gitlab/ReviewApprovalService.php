<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;

class ReviewApprovalService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly GitlabApiProvider $apiProvider, private readonly ReviewMergeRequestService $mergeRequestService)
    {
    }

    /**
     * @throws Throwable
     */
    public function approve(CodeReview $review, CodeReviewer $reviewer, bool $approve): void
    {
        $projectId = (int)$review->getRepository()->getRepositoryProperty('gitlab-project-id');
        $api       = $this->apiProvider->create($review->getRepository(), $reviewer->getUser());
        if ($api === null || $projectId === 0) {
            $this->logger?->info('ReviewApprovalService: No api configuration found for reviewer {id}', ['id' => $reviewer->getId()]);

            return;
        }

        $mergeRequestIId = $this->mergeRequestService->retrieveMergeRequestIID($api, $review);
        if ($mergeRequestIId === null) {
            $this->logger?->info('ReviewApprovalService: No mergeRequestIdd found for review {id}', ['id' => $review->getId()]);

            return;
        }

        if ($approve) {
            $this->logger?->info('ReviewApprovalService: Approving merge request {id}', ['id' => $mergeRequestIId]);
            $api->mergeRequests()->approve($projectId, $mergeRequestIId);
        } else {
            $this->logger?->info('ReviewApprovalService: Unapproving merge request {id}', ['id' => $mergeRequestIId]);
            $api->mergeRequests()->unapprove($projectId, $mergeRequestIId);
        }
    }
}
