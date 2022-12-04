<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Review;

use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Message\UserAwareInterface;

interface CodeReviewEventInterface extends UserAwareInterface, CodeReviewAwareInterface
{
}
