<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Mail;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Git\Commit;

class MailSubjectFormatter
{
    /**
     * @param Commit[] $commits
     */
    public function format(string $subject, Rule $rule, array $commits): string
    {
        $authors      = [];
        $repositories = [];
        foreach ($commits as $commit) {
            $authors[]      = $commit->author->name;
            $repositories[] = $commit->repository->getName();
        }

        $search  = ['{name}', '{authors}', '{repositories}'];
        $replace = [
            $rule->getName() ?? '',
            implode(', ', array_unique(array_filter($authors))),
            implode(', ', array_unique(array_filter($repositories)))
        ];

        return str_ireplace($search, $replace, $subject);
    }
}
