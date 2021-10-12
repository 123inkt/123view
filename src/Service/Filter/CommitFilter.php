<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
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
     * @param Commit[] $commits
     *
     * @return Commit[]
     */
    public function exclude(array $commits, Definition $definition): array
    {
        $authors = $definition->getAuthors();

        foreach ($commits as $commitIndex => $commit) {
            // filter all commits from the given author
            if (in_array($commit->author->email, $authors, true)) {
                $this->log->debug(sprintf('Commit filter: commit %s by %s', $commit->commitHashes[0] ?? '', $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter all subjects that should be excluded
            if ($this->subjectMatcher->matches($commit, $definition)) {
                $this->log->debug(sprintf('Commit filter: on subject: commit %s by %s', $commit->subject, $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter out file and directory matches
            $this->fileFilter($commit, $definition, true);

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
     * @param Commit[] $commits
     *
     * @return Commit[]
     */
    public function include(array $commits, Definition $definition): array
    {
        $authors = $definition->getAuthors();

        foreach ($commits as $commitIndex => $commit) {
            // only include authors specified, or any if none
            if (count($authors) > 0 && in_array($commit->author->email, $authors, true) === false) {
                $this->log->debug(sprintf('Commit filter: commit %s by %s', $commit->commitHashes[0], $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter all subjects that should be excluded
            if (count($definition->getSubjects()) > 0 && $this->subjectMatcher->matches($commit, $definition) === false) {
                $this->log->debug(sprintf('Commit filter: on subject: commit %s by %s', $commit->subject, $commit->author->email));
                unset($commits[$commitIndex]);
                continue;
            }

            // filter out file and directory matches
            if (count($definition->getFiles()) > 0) {
                $this->fileFilter($commit, $definition, false);
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

    private function fileFilter(Commit $commit, Definition $definition, bool $exclude): void
    {
        // filter out file and directory matches
        foreach ($commit->files as $fileIndex => $file) {
            if ($this->fileMatcher->matches($file, $definition) === $exclude) {
                $this->log->debug(sprintf('Commit filter: file %s', $file->getFilename()));
                unset($commit->files[$fileIndex]);
            }
        }
    }
}
