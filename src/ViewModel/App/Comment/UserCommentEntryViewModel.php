<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;

/**
 * A comment of the user, or a reply of the user together with the comment it was placed on.
 */
readonly class UserCommentEntryViewModel
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public Comment $comment, public ?CommentReply $reply = null)
    {
    }
}
