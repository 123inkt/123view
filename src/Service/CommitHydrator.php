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
        $author     = new Author($data[FormatPattern::AUTHOR_NAME], $data[FormatPattern::AUTHOR_EMAIL]);
        $authorDate = new Carbon($data[FormatPattern::AUTHOR_DATE_ISO8601]);
        $refs       = trim($data[FormatPattern::REF_NAMES]);
        if ($refs === '') {
            $refs = trim($data[FormatPattern::REF_NAME_SOURCE]);
        }

        return new Commit(
            $repository,
            $data[FormatPattern::PARENT_HASH],
            $data[FormatPattern::COMMIT_HASH],
            $author,
            $authorDate,
            $data[FormatPattern::SUBJECT],
            trim($data[FormatPattern::BODY]),
            $refs === '' ? null : $refs,
            $files,
        );
    }
}
