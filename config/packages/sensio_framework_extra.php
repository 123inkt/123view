<?php
declare(strict_types=1);

use Symfony\Config\SensioFrameworkExtraConfig;

return static function (SensioFrameworkExtraConfig $extraConfig): void {
    $extraConfig->router()->annotations(false);
};
