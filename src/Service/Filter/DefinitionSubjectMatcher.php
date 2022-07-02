<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use Doctrine\Common\Collections\Collection;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Git\Commit;
use RuntimeException;

class DefinitionSubjectMatcher
{
    /**
     * @param Collection<int, Filter> $filters
     * @suppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function matches(Commit $commit, Collection $filters): bool
    {
        /** @var Filter $filter */
        foreach ($filters as $filter) {
            $result = @preg_match((string)$filter->getPattern(), $commit->subject);
            if ($result === false) {
                throw new RuntimeException('Invalid regex pattern in subject pattern: ' . $filter->getPattern());
            }
            if ($result === 1) {
                return true;
            }
        }

        return false;
    }
}
