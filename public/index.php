<?php
declare(strict_types=1);

use DR\Review\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    if ($context['APP_ENV'] === 'dev') {
        umask(0000);
    }

    return new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
};
