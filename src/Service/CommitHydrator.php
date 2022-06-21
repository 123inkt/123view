<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service;

use DateTime;
use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Git\FormatPattern;
use Exception;

class CommitHydrator
{
    /**
     * @param string[]   $data
     * @param DiffFile[] $files
     *
     * @throws Exception
     */
    public function hydrate(Repository $repository, array $data, array $files): Commit
    {
        $author     = new Author((string)$data[FormatPattern::AUTHOR_NAME], (string)$data[FormatPattern::AUTHOR_EMAIL]);
        $authorDate = new DateTime((string)$data[FormatPattern::AUTHOR_DATE_ISO8601]);
        $refs       = trim((string)$data[FormatPattern::REF_NAMES]);

        return new Commit(
            $repository,
            (string)$data[FormatPattern::PARENT_HASH],
            (string)$data[FormatPattern::COMMIT_HASH],
            $author,
            $authorDate,
            (string)$data[FormatPattern::SUBJECT],
            $refs === '' ? null : $refs,
            $files,
        );
    }
}
