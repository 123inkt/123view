<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class CommentNotInReviewException extends RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(private readonly int $commentId, private readonly int $reviewId)
    {
        parent::__construct(sprintf('Comment %d does not belong to review %d.', $this->commentId, $this->reviewId));
    }

    public function getToolCallResult(): string
    {
        return sprintf('Comment %d does not belong to review %d.', $this->commentId, $this->reviewId);
    }
}
