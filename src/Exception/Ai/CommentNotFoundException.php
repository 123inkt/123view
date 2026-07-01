<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class CommentNotFoundException extends RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(private readonly int $commentId)
    {
        parent::__construct('Comment not found: ' . $this->commentId);
    }

    public function getToolCallResult(): string
    {
        return sprintf('Comment %d not found.', $this->commentId);
    }
}
