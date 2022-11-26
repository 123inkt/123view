<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use Doctrine\Common\Collections\Collection;
use DR\GitCommitNotification\Doctrine\Type\FilterType;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Git\Commit;
use Psr\Log\LoggerInterface;

class CommitFilter
{
    private DefinitionFileMatcher    $fileMatcher;
    private LoggerInterface          $log;
    private DefinitionSubjectMatcher $subjectMatcher;

    public function __construct(LoggerInterface $log, DefinitionFileMatcher $fileMatcher, DefinitionSubjectMatcher $subjectMatcher)
    {
        $this->log            = $log;
        $this->fileMatcher    = $fileMatcher;
        $this->subjectMatcher = $subjectMatcher;
    }

    /**
     * @param Commit[]                $commits
     * @param Collection<int, Filter> $filters
     *
     * @return Commit[]
     */
    public function exclude(array $commits, Collection $filters): array
    {
        $authors  = $filters->filter(static fn($filter) => $filter->getType() === FilterType::AUTHOR);
        $subjects = $filters->filter(static fn($filter) => $filter->getType() === FilterType::SUBJECT);
        $files    = $filters->filter(static fn($filter) => $filter->getType() === FilterType::FILE);

        foreach ($commits as $commitIndex => $commit) {
            // filter all commits from the given author
            if ($authors->exists(static fn($i, $filter) => $filter->getPattern() === $commit->author->email)) {
                $this->log->debug(sprintf('Commit filter: commit %s by %s', $commit->commitHashes[0] ?? '', $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter all subjects that should be excluded
            if ($this->subjectMatcher->matches($commit, $subjects)) {
                $this->log->debug(sprintf('Commit filter: on subject: commit %s by %s', $commit->subject, $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter out file and directory matches
            $this->fileFilter($commit, $files, true);

            // filter out commit if all file got filtered
            if (count($commit->files) !== 0) {
                continue;
            }

            $this->log->debug(sprintf('Commit filter: commit %s because all files were filtered', $commit->commitHashes[0]));
            unset($commits[$commitIndex]);
        }

        return $commits;
    }

    /**
     * @param Commit[]                $commits
     * @param Collection<int, Filter> $filters
     *
     * @return Commit[]
     */
    public function include(array $commits, Collection $filters): array
    {
        $authors  = $filters->filter(static fn(Filter $filter) => $filter->getType() === FilterType::AUTHOR);
        $subjects = $filters->filter(static fn(Filter $filter) => $filter->getType() === FilterType::SUBJECT);
        $files    = $filters->filter(static fn(Filter $filter) => $filter->getType() === FilterType::FILE);

        foreach ($commits as $commitIndex => $commit) {
            // only include authors specified, or any if none
            if (count($authors) > 0 && $authors->exists(static fn($i, $filter) => $filter->getPattern() === $commit->author->email) === false) {
                $this->log->debug(sprintf('Commit filter: commit %s by %s', $commit->commitHashes[0], $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter all subjects that should be excluded
            if (count($subjects) > 0 && $this->subjectMatcher->matches($commit, $subjects) === false) {
                $this->log->debug(sprintf('Commit filter: on subject: commit %s by %s', $commit->subject, $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter out file and directory matches
            if (count($files) > 0) {
                $this->fileFilter($commit, $files, false);
            }

            // filter out commit if all file got filtered
            if (count($commit->files) !== 0) {
                continue;
            }

            $this->log->debug(sprintf('Commit filter: commit %s because all files were filtered', $commit->commitHashes[0]));
            unset($commits[$commitIndex]);
        }

        return $commits;
    }

    /**
     * @param Collection<int, Filter> $filters
     */
    private function fileFilter(Commit $commit, Collection $filters, bool $exclude): void
    {
        // filter out file and directory matches
        foreach ($commit->files as $fileIndex => $file) {
            if ($this->fileMatcher->matches($file, $filters) === $exclude) {
                $this->log->debug(sprintf('Commit filter: file %s', $file->getFilename()));
                unset($commit->files[$fileIndex]);
            }
        }
    }
}
