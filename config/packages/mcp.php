<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'mcp' => [
        'app'               => '123view',
        'version'           => '1.0.0',
        'description'       => 'A code review and commit notification application',
        'client_transports' => ['http' => true],
        'discovery'         => ['scan_dirs' => ['src/Service/Ai/Mcp', 'src/Service/Ai/Tool']],
        'instructions'      => 'This server provides access to code reviews'
    ],
]);
