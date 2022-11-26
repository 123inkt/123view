<?php
declare(strict_types=1);

use Symfony\Config\MakerConfig;

return static function (MakerConfig $maker) {
    $maker->rootNamespace('DR\GitCommitNotification');
};
