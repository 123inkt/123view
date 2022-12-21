<?php
declare(strict_types=1);

namespace DR\Review\Service\Filter;

use Doctrine\Common\Collections\ReadableCollection;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Filter;
use RuntimeException;

class DefinitionSubjectMatcher
{
    /**
     * @param ReadableCollection<int, Filter> $filters
     * @suppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function matches(Commit $commit, ReadableCollection $filters): bool
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
