<?php
declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'web_profiler' => ['toolbar' => ['enabled' => true,], 'intercept_redirects' => false],
    'framework'    => ['profiler' => ['only_exceptions' => false]],
]);
