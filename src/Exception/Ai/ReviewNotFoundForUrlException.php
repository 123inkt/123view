<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class ReviewNotFoundForUrlException extends RuntimeException implements ToolExecutionExceptionInterface
{
    public function __construct(private readonly string $repositoryName, private readonly int $projectId)
    {
        parent::__construct(sprintf('Review cr-%d not found in repository: %s', $this->projectId, $this->repositoryName));
    }

    public function getToolCallResult(): string
    {
        return sprintf('No review cr-%d exists in repository \'%s\'.', $this->projectId, $this->repositoryName);
    }
}
