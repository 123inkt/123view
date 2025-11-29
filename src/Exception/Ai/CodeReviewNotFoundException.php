<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class CodeReviewNotFoundException extends RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(private readonly int $reviewId)
    {
        parent::__construct();
    }

    public function getToolCallResult(): string
    {
        return sprintf('Code review %d not found.', $this->reviewId);
    }
}
