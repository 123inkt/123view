<?php

declare(strict_types=1);

use Symfony\Config\SymfonyTraceConfig;

return static function (SymfonyTraceConfig $config): void {
    $config->trustRequestHeader(false);
    $config->sendResponseHeader(true);
    $config->enableMonolog(true);
    $config->console()->enabled(true);
    $config->enableMessenger(true);
    $config->enableTwig(true);
    $config->httpClient()->enabled(false);
};
