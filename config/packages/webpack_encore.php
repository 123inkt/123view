<?php
declare(strict_types=1);

use Symfony\Config\WebpackEncoreConfig;

return static function (WebpackEncoreConfig $config): void {
    $config->outputPath('%kernel.project_dir%/public/build');
    $config->scriptAttributes('defer', true);
};
