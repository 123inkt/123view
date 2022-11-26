<?php
declare(strict_types=1);

use DR\GitCommitNotification\Entity\Config\ExternalLink;

return [
    (new ExternalLink())
        ->setPattern('B#{}')
        ->setUrl('https://example.com/detectives/issue/{}')
];
