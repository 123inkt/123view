<?php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

(new Dotenv())->loadEnv(dirname(__DIR__) . '/.env');
ErrorHandler::register(null, false);
