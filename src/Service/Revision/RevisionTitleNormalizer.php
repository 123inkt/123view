<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Revision;

class RevisionTitleNormalizer
{
    public function normalize(string $title): string
    {
        return preg_replace('/^Revert\s+"(.*)"$/', '$1', trim($title));
    }
}
