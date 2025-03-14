<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

class RevisionTitleNormalizer
{
    public function normalize(string $title): string
    {
        for ($i = 0; $i < 10; $i++) {
            $titleBefore = $title;
            $title       = (string)preg_replace('/^(Revert|Reapply)\s+"(.*)"$/', '$2', trim($title));

            if ($titleBefore === $title) {
                break;
            }
        }

        return $title;
    }
}
