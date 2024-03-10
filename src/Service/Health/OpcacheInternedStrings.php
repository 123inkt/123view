<?php

declare(strict_types=1);

namespace DR\Review\Service\Health;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Override;

/**
 * @codeCoverageIgnore relies on native opcache_get_status
 */
class OpcacheInternedStrings extends AbstractCheck
{
    private const FAILURE = 5 * 1024;
    private const WARNING = 50 * 1024;

    #[Override]
    public function check(): ResultInterface
    {
        if (function_exists('opcache_get_status') === false) {
            return new Failure('opcache extension is not enabled');
        }

        $opcacheStatus = opcache_get_status(false);
        if (isset($opcacheStatus['interned_strings_usage']['free_memory']) === false) {
            return new Failure('opcache.interned_strings_usage.free_memory is not set');
        }

        $freeMemory = (int)$opcacheStatus['interned_strings_usage']['free_memory'];
        if ($freeMemory < self::FAILURE) {
            $result = new Failure('opcache.interned_strings_usage.free_memory is less than 5KB. Remaining: ' . $freeMemory . ' bytes');
            $result->setData($opcacheStatus['interned_strings_usage']);

            return $result;
        }

        if ($freeMemory < self::WARNING) {
            $result = new Warning('opcache.interned_strings_usage.free_memory is less than 50KB. Remaining: ' . $freeMemory . ' bytes');
            $result->setData($opcacheStatus['interned_strings_usage']);

            return $result;
        }

        return new Success();
    }
}
