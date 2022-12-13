<?php
declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->assets()->enabled(true)->version('%env(APP_ASSET_VERSION)%');
};
