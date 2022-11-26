<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Model\Review\Action;

use DR\GitCommitNotification\Entity\Review\CommentReply;

/**
 * @codeCoverageIgnore
 */
class EditCommentReplyAction extends AbstractReviewAction
{
    public function __construct(public readonly ?CommentReply $reply)
    {
        parent::__construct(self::ACTION_EDIT_REPLY);
    }
}
