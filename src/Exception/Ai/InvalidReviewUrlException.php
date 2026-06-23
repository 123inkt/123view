<?php
declare(strict_types=1);

namespace DR\Review\Exception\Ai;

use RuntimeException;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class InvalidReviewUrlException extends RuntimeException implements ToolExecutionExceptionInterface
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(private readonly string $url)
    {
        parent::__construct('Invalid review URL: ' . $this->url);
    }

    public function getToolCallResult(): string
    {
        return sprintf(
            'Invalid review URL: %s. Expected a URL like https://<host>/app/<repository>/review/cr-<number>.',
            $this->url
        );
    }
}
