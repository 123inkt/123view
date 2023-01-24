<?php
declare(strict_types=1);

namespace DR\Review\Service;

use Carbon\Carbon;
use DR\Review\Entity\Git\Author;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\FormatPattern;
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
        $authorDate = Carbon::parse((string)$data[FormatPattern::AUTHOR_DATE_ISO8601]);
        $refs       = trim((string)$data[FormatPattern::REF_NAMES]);

        return new Commit(
            $repository,
            (string)$data[FormatPattern::PARENT_HASH],
            (string)$data[FormatPattern::COMMIT_HASH],
            $author,
            $authorDate,
            (string)$data[FormatPattern::SUBJECT],
            trim((string)$data[FormatPattern::BODY]),
            $refs === '' ? null : $refs,
            $files,
        );
    }
}
