<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\Action;

use DR\Review\Entity\Review\Comment;

/**
 * @codeCoverageIgnore
 */
class EditCommentAction extends AbstractReviewAction
{
    public function __construct(public readonly ?Comment $comment)
    {
        parent::__construct(self::ACTION_EDIT_COMMENT);
    }
}
