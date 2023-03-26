<?php
declare(strict_types=1);

namespace DR\Review\Security;

enum SessionKeys: string
{
    case REVIEW_DIFF_MODE = 'review-diff-mode';
    case REVIEW_COMMENT_VISIBILITY = 'review-comment-visibility';
    case DIFF_COMPARISON_POLICY = 'diff-comparison-policy';
}
