<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Entity\User\User;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use Symfony\Bundle\SecurityBundle\Security;

class UserReviewSettingsProvider
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getVisibleLines(): int
    {
        $user = $this->security->getUser();
        if ($user instanceof User === false) {
            return 6;
        }

        return $user->getReviewSetting()->getDiffVisibleLines();
    }

    public function getComparisonPolicy(): DiffComparePolicy
    {
        $user = $this->security->getUser();
        if ($user instanceof User === false) {
            return DiffComparePolicy::ALL;
        }

        return $user->getReviewSetting()->getDiffComparisonPolicy();
    }

    public function getReviewDiffMode(): ReviewDiffModeEnum
    {
        $user = $this->security->getUser();
        if ($user instanceof User === false) {
            return ReviewDiffModeEnum::INLINE;
        }

        return $user->getReviewSetting()->getReviewDiffMode();
    }

    public function getCommentVisibility(): CommentVisibilityEnum
    {
        $user = $this->security->getUser();
        if ($user instanceof User === false) {
            return CommentVisibilityEnum::ALL;
        }

        return $user->getReviewSetting()->getReviewCommentVisibility();
    }
}
