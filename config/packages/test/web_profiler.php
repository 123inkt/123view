<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;
use Symfony\Config\WebProfilerConfig;

return static function (WebProfilerConfig $profiler, FrameworkConfig $framework): void {
    $profiler->toolbar(false)->interceptRedirects(false);
    $framework->profiler()->collect(false);
};
