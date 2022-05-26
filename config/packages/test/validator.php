<?php

declare(strict_types=1);

use Symfony\Config\Framework\ValidationConfig;

return static function (ValidationConfig $validationConfig): void {
    $validationConfig->notCompromisedPassword()->enabled(false);
};
