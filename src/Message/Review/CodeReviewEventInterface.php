<?php
declare(strict_types=1);

namespace DR\Review\Message\Review;

use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Message\UserAwareInterface;

interface CodeReviewEventInterface extends UserAwareInterface, CodeReviewAwareInterface
{
}
