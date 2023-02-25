<?php
declare(strict_types=1);

use DR\Review\Entity\Config\ExternalLink;

return [
    (new ExternalLink())
        ->setPattern('B#{}')
        ->setUrl('https://example.com/detectives/issue/{}')
];
