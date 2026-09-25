<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\App\Review\Timeline;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Revision\Revision;

class TimelineEntryViewModel
{
    private ?Comment      $comment  = null;
    private ?CommentReply $reply    = null;
    private ?Revision     $revision = null;

    /**
     * @param non-empty-array<CodeReviewActivity> $activities
     */
    public function __construct(public readonly array $activities, public readonly string $message, public readonly ?string $url)
    {
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function getReply(): ?CommentReply
    {
        return $this->reply;
    }

    public function setCommentOrReply(Comment|CommentReply|null $comment): self
    {
        if ($comment instanceof Comment) {
            $this->comment = $comment;
        } elseif ($comment instanceof CommentReply) {
            $this->reply = $comment;
        }

        return $this;
    }

    public function getRevision(): ?Revision
    {
        return $this->revision;
    }

    public function setRevision(?Revision $revision): self
    {
        $this->revision = $revision;

        return $this;
    }
}
