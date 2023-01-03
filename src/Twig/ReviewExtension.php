<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Entity\Git\Diff\DiffFile;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ReviewExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('review_file_path', [$this, 'filePath'])];
    }

    public function filePath(DiffFile $file): string
    {
        $filepath = $file->getPathname();
        if ($file->hashEnd !== null) {
            $filepath .= ':' . $file->hashEnd;
        }

        return $filepath;
    }
}
