<?php

declare(strict_types=1);

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $dbal = $doctrineConfig->dbal()->connection('default');
    $dbal->dbnameSuffix('%env(default::TEST_TOKEN)%');
};
