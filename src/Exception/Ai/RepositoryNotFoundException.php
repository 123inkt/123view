<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class RepositoryNotFoundException extends RuntimeException implements ToolExecutionExceptionInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(private readonly string $repositoryName)
    {
        parent::__construct('Repository not found: ' . $this->repositoryName);
    }

    public function getToolCallResult(): string
    {
        return sprintf('No repository named \'%s\' was found.', $this->repositoryName);
    }
}
