<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;

class CodeReviewerStateResolver
{
    /**
     * Review is rejected when atleast 1 reviewer rejected
     * Review is accepted when _all_ reviewers accepted, but only when the review is not self-reviewed
     * Review is open in other cases
     *
     * @return CodeReviewerStateType::OPEN|CodeReviewerStateType::REJECTED|CodeReviewerStateType::ACCEPTED
     */
    public function getReviewersState(CodeReview $review): string
    {
        /** @var array<string, int> $authors lookup table of author email-addresses */
        $authors   = array_flip(array_map(static fn(Revision $revision) => $revision->getAuthorEmail(), $review->getRevisions()->toArray()));
        $reviewers = $review->getReviewers();

        if (count($reviewers) === 0) {
            return CodeReviewerStateType::OPEN;
        }

        $accepted     = [];
        $selfAccepted = [];
        foreach ($reviewers as $reviewer) {
            if ($reviewer->getState() === CodeReviewerStateType::REJECTED) {
                return CodeReviewerStateType::REJECTED;
            }

            if ($reviewer->getState() !== CodeReviewerStateType::ACCEPTED) {
                continue;
            }

            if (isset($authors[$reviewer->getUser()->getEmail()])) {
                $selfAccepted[] = $reviewer;
            }
            $accepted[] = $reviewer;
        }

        // All reviewers have accepted
        // Self-accepted must not equal reviewers, unless reviewers is greater than 1
        $isReviewAccepted = count($accepted) === count($reviewers) && (count($selfAccepted) !== count($reviewers) || count($selfAccepted) > 1);

        return $isReviewAccepted ? CodeReviewerStateType::ACCEPTED : CodeReviewerStateType::OPEN;
    }
}
