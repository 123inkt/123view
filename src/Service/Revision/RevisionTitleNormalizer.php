<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Revision;

class RevisionTitleNormalizer
{
    public function normalize(string $title): string
    {
        for ($i = 0; $i < 10; $i++) {
            $titleBefore = $title;
            $title       = (string)preg_replace('/^Revert\s+"(.*)"$/', '$1', trim($title));

            if ($titleBefore === $title) {
                break;
            }
        }

        return $title;
    }
}
