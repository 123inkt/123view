<?php
declare(strict_types=1);

namespace DR\Review\Security;

enum SessionKeys: string
{
    case REVIEW_DIFF_MODE = 'review-diff-mode';
}
