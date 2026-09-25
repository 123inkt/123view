<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'liip_test_fixtures' => [
        'keep_database_and_schema' => true,
        'cache_metadata'           => false,
    ],
]);
