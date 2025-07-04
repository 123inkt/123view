<?php

declare(strict_types=1);

use Symfony\Config\SymfonyTraceConfig;

return static function (SymfonyTraceConfig $config): void {
    $config->request()->trustHeader(true);
    $config->response()->sendHeader(true);
    //$config->monolog()->enabled(true);
    $config->console()->enabled(true);
    //$config->messenger()->enabled(true);
    //$config->twig()->enabled(true);
    $config->httpClient()->enabled(false);
};
