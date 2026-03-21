<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'web_profiler' => [
        'toolbar'             => ['enabled' => false],
        'intercept_redirects' => false,
    ],
    'framework'    => ['profiler' => ['collect' => false]],
]);
