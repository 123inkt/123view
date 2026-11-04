<?php
declare(strict_types=1);

namespace DR\Review\Monolog;

use DR\Review\Exception\Review\ReviewException;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\LogRecord;

class ToolExecutionExceptionActivationStrategy implements ActivationStrategyInterface
{
    public function __construct(private readonly ActivationStrategyInterface $inner)
    {
    }

    public function isHandlerActivated(LogRecord $record): bool
    {
        if (($record->context['exception'] ?? null) instanceof ReviewException) {
            return false;
        }

        return $this->inner->isHandlerActivated($record);
    }
}
