<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;

readonly class CommentOverviewItem
{
    public readonly bool $isReply;
    public readonly Comment $comment;
    public readonly Comment|CommentReply $content;

    public function __construct(Comment|CommentReply $content)
    {
        $this->content = $content;

        if ($content instanceof CommentReply) {
            $this->isReply = true;
            $this->comment = $content->getComment();
        } else {
            $this->isReply = false;
            $this->comment = $content;
        }
    }
}
