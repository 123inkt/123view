<?php
declare(strict_types=1);

namespace DR\Review\Service\Filter;

use Doctrine\Common\Collections\ReadableCollection;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Filter;
use RuntimeException;

class DefinitionFileMatcher
{
    /**
     * @param ReadableCollection<int, Filter> $filters
     */
    public function matches(DiffFile $file, ReadableCollection $filters): bool
    {
        $filepath = $file->filePathAfter ?? $file->filePathBefore;
        if ($filepath === null) {
            return false;
        }

        foreach ($filters as $filter) {
            $result = @preg_match($filter->getPattern(), $filepath);
            if ($result === false) {
                throw new RuntimeException('Invalid regex pattern in file pattern: ' . $filter->getPattern());
            }
            if ($result === 1) {
                return true;
            }
        }

        return false;
    }
}
