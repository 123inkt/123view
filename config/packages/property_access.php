<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'framework' => [
        'property_access' => [
            'magic_call'                               => false,
            'magic_get'                                => false,
            'magic_set'                                => false,
            // must be false to allow CollectionType: allow_add.
            'throw_exception_on_invalid_index'         => false,
            'throw_exception_on_invalid_property_path' => true,
        ],
    ],
]);
