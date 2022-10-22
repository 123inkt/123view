<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Model\Review\Action;

use DR\GitCommitNotification\Entity\Review\Comment;

class EditCommentAction extends AbstractReviewAction
{
    public function __construct(public readonly ?Comment $comment)
    {
        parent::__construct(self::ACTION_EDIT_COMMENT);
    }
}
