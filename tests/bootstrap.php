<?php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Filesystem\Filesystem;

(new Filesystem())->remove([dirname(__DIR__) . '/var/build/test', dirname(__DIR__) . '/var/cache/test']);
(new Dotenv())->loadEnv(dirname(__DIR__) . '/.env');
ErrorHandler::register(null, false);
