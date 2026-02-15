<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'symfony_trace' => [
        'request'     => ['trust_header' => true],
        'response'    => ['send_header' => true],
        'monolog'     => ['enabled' => true],
        'console'     => ['enabled' => true],
        'messenger'   => ['enabled' => true],
        'twig'        => ['enabled' => true],
        'http_client' => ['enabled' => false],
    ],
]);
