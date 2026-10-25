<?php
declare(strict_types=1);

namespace DR\Review\Monolog;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\LogRecord;
use Symfony\AI\Agent\Toolbox\Exception\ToolExecutionExceptionInterface;

class ToolExecutionExceptionActivationStrategy implements ActivationStrategyInterface
{
    public function __construct(private readonly ActivationStrategyInterface $inner)
    {
    }

    public function isHandlerActivated(LogRecord $record): bool
    {
        if (($record->context['exception'] ?? null) instanceof ToolExecutionExceptionInterface) {
            return false;
        }

        return $this->inner->isHandlerActivated($record);
    }
}
