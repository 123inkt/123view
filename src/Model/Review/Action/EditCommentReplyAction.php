<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\Action;

use DR\Review\Entity\Review\CommentReply;

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
