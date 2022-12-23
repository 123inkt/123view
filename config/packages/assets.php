<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->assets()->enabled(true)->jsonManifestPath('%kernel.project_dir%/public/build/manifest.json');
};
