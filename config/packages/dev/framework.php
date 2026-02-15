<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config(['framework' => ['ide' => '%env(IDE_URL_PATTERN)%&%env(IDE_URL_PATH_MAP)%']]);
