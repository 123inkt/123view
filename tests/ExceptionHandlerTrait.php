<?php

declare(strict_types=1);

namespace DR\Review\Tests;

use Symfony\Component\ErrorHandler\ErrorHandler;

trait ExceptionHandlerTrait
{
    protected function getExceptionHandlers(): array
    {
        $res = [];

        while (true) {
            $previousHandler = set_exception_handler(static fn() => null);
            restore_exception_handler();

            if ($previousHandler === null) {
                break;
            }

            $res[] = $previousHandler;
            restore_exception_handler();
        }

        foreach (array_reverse($res) as $handler) {
            set_exception_handler($handler);
        }

        return $res;
    }

    protected function restoreExceptionHandler(): void
    {
        $res = [];

        while (true) {
            $previousHandler = set_exception_handler(static fn() => null);
            restore_exception_handler();

            if (is_array($previousHandler) && $previousHandler[0] instanceof ErrorHandler && $previousHandler[1] === 'handleException') {
                restore_exception_handler();
                continue;
            }

            if ($previousHandler === null) {
                break;
            }

            $res[] = $previousHandler;
            restore_exception_handler();
        }

        foreach (array_reverse($res) as $handler) {
            set_exception_handler($handler);
        }
    }
}
