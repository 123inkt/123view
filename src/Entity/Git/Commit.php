<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git;

use DateTime;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;

class Commit
{
    public Repository $repository;
    public string $parentHash;
    /** @var string[] */
    public array    $commitHashes;
    public Author   $author;
    public DateTime $date;
    public string   $subject;
    public ?string  $refs;
    /** @var DiffFile[] */
    public array $files;
    /** @var IntegrationLink[] */
    public array $integrationLinks = [];

    /**
     * @param DiffFile[] $files
     */
    public function __construct(
        Repository $repository,
        string $parentHash,
        string $commitHash,
        Author $author,
        DateTime $date,
        string $subject,
        ?string $refs,
        array $files
    ) {
        $this->repository   = $repository;
        $this->parentHash   = $parentHash;
        $this->commitHashes = [$commitHash];
        $this->author       = $author;
        $this->date         = $date;
        $this->subject      = $subject;
        $this->refs         = $refs;
        $this->files        = $files;
    }

    /**
     * Get the name of the repository by removing the .git suffix (if any), and stripping the base of the uri.
     */
    public function getRepositoryName(): string
    {
        $repository = (string)preg_replace('/\.git$/', '', (string)$this->repository->getUrl());

        return basename($repository);
    }

    /**
     * Get the first line (if any) of the commit subject
     */
    public function getSubjectLine(): string
    {
        return explode("\n", $this->subject)[0] ?? '';
    }

    public function getRemoteRef(): ?string
    {
        if ($this->refs === null) {
            return null;
        }

        if (preg_match('#refs/remotes/origin/([\w.-]+)#', $this->refs, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
