<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Entity\Git\Diff\DiffFile;
use Twig\Attribute\AsTwigFilter;

class ReviewExtension
{
    #[AsTwigFilter(name: 'review_file_path')]
    public function filePath(DiffFile $file): string
    {
        $filepath = $file->getPathname();
        if ($file->hashEnd !== null) {
            $filepath .= ':' . $file->hashEnd;
        }

        return $filepath;
    }
}
