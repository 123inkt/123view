<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config(['fd_log_viewer' => ['log_files' => ['monolog' => ['downloadable' => true]]]]);
