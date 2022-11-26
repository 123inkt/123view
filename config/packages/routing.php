<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->router()
        ->enabled(true)
        ->strictRequirements(true)
        ->utf8(true);
};
