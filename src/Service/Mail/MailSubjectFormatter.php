<?php
declare(strict_types=1);

namespace DR\Review\Service\Mail;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Rule;

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
            implode(', ', array_unique(array_filter($authors, static fn($val) => $val !== '' && $val !== null))),
            implode(', ', array_unique(array_filter($repositories, static fn($val) => $val !== '' && $val !== null)))
        ];

        return str_ireplace($search, $replace, $subject);
    }
}
