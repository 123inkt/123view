<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Utils\Assert;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[McpTool('reject_review', 'Reject a code review as the current user. The user is added as a reviewer if not already assigned.')]
readonly class RejectReviewTool
{
    public function __construct(
        private Security $security,
        private CodeReviewRepository $reviewRepository,
        private ChangeReviewerStateService $changeReviewerStateService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The review id of the code review to reject', minimum: 1)] int $codeReviewId): string
    {
        $review = $this->reviewRepository->find($codeReviewId);
        if ($review === null) {
            throw new CodeReviewNotFoundException($codeReviewId);
        }

        $this->changeReviewerStateService->changeState(
            $review,
            Assert::isInstanceOf($this->security->getUser(), User::class),
            CodeReviewerStateType::REJECTED
        );

        return sprintf('Review rejected. Reviewers state: %s', $review->getReviewersState());
    }
}
