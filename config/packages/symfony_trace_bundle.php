<?php

declare(strict_types=1);

use Symfony\Config\SymfonyTraceConfig;

return static function (SymfonyTraceConfig $config): void {
    $config->request()->trustHeader(true);
    $config->response()->sendHeader(true);
    $config->enableMonolog(true);
    $config->console()->enabled(true);
    $config->enableMessenger(true);
    $config->enableTwig(true);
    $config->httpClient()->enabled(false);
};
