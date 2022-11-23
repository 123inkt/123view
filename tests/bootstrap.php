<?php
declare(strict_types=1);

use DG\BypassFinals;
use Symfony\Component\Dotenv\Dotenv;

BypassFinals::enable();
(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test');
