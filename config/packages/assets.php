<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;
use Symfony\Config\WebProfilerConfig;

return static function (FrameworkConfig $framework): void {
    $framework->assets()->enabled(false);
};
