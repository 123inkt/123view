<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use RuntimeException;

class DefinitionFileMatcher
{
    /**
     * @suppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function matches(DiffFile $file, Definition $definition): bool
    {
        $filepath = $file->filePathAfter ?? $file->filePathBefore;
        if ($filepath === null) {
            return false;
        }

        foreach ($definition->getFiles() as $pattern) {
            $result = @preg_match($pattern, $filepath);
            if ($result === false) {
                throw new RuntimeException('Invalid regex pattern in file pattern: ' . $pattern);
            }
            if ($result === 1) {
                return true;
            }
        }

        return false;
    }
}
