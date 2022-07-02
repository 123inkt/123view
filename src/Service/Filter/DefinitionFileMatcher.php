<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use Doctrine\Common\Collections\Collection;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use RuntimeException;

class DefinitionFileMatcher
{
    /**
     * @param Collection<int, Filter> $filters
     * @suppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function matches(DiffFile $file, Collection $filters): bool
    {
        $filepath = $file->filePathAfter ?? $file->filePathBefore;
        if ($filepath === null) {
            return false;
        }

        foreach ($filters as $filter) {
            $result = @preg_match((string)$filter->getPattern(), $filepath);
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
