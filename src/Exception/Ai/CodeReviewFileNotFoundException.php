<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class CodeReviewFileNotFoundException extends RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(private readonly string $filepath, private readonly int $reviewId)
    {
        parent::__construct();
    }

    public function getToolCallResult(): string
    {
        return sprintf('Filepath %s not found in review %d.', $this->filepath, $this->reviewId);
    }
}
