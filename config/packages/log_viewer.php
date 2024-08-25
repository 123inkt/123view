<?php

declare(strict_types=1);

use Symfony\Config\FdLogViewerConfig;

return static function (FdLogViewerConfig $config): void {
    $config->logFiles('monolog')->downloadable(true);
};
