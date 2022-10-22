<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Model\Review\Action;

use DR\GitCommitNotification\Entity\Review\LineReference;

class AddCommentAction extends AbstractReviewAction
{
    public function __construct(public readonly LineReference $lineReference)
    {
        parent::__construct(self::ACTION_ADD_COMMENT);
    }
}
