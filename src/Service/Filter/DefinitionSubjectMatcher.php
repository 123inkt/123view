<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Entity\Git\Commit;
use RuntimeException;

class DefinitionSubjectMatcher
{
    /**
     * @suppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function matches(Commit $commit, Definition $definition): bool
    {
        foreach ($definition->getSubjects() as $pattern) {
            $result = @preg_match($pattern, $commit->subject);
            if ($result === false) {
                throw new RuntimeException('Invalid regex pattern in subject pattern: ' . $pattern);
            }
            if ($result === 1) {
                return true;
            }
        }

        return false;
    }
}
