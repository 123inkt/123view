<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Repository\Review\CommentRepository;

/**
 * Prevents duplicate AI-authored comments on the same file/line with equivalent content, so a
 * retried per-file review (e.g. after a message handler retry) cannot post the same finding twice.
 */
class AiCommentDeduplicationChecker
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    public function isDuplicate(CodeReview $review, string $filepath, LineReference $lineReference, string $message): bool
    {
        $normalizedMessage  = $this->normalize($message);
        $normalizedLineRef  = (string)$lineReference;

        foreach ($this->commentRepository->findByReview($review, [$filepath]) as $comment) {
            if ((string)$comment->getLineReference() !== $normalizedLineRef) {
                continue;
            }
            if ($this->normalize($comment->getMessage()) === $normalizedMessage) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $message): string
    {
        return strtolower(trim((string)preg_replace('/\s+/', ' ', $message)));
    }
}
