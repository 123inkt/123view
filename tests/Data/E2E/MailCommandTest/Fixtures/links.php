<?php
declare(strict_types=1);

use DR\GitCommitNotification\Entity\Config\ExternalLink;

$link = new ExternalLink();
$link->setPattern('B#{}');
$link->setUrl('https://example.com/detectives/issue/{}');

return [$link];
