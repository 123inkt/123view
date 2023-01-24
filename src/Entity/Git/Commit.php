<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git;

use Carbon\Carbon;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;

class Commit
{
    public Repository $repository;
    public string     $parentHash;
    /** @var string[] */
    public array   $commitHashes;
    public Author  $author;
    public Carbon  $date;
    public string  $subject;
    public ?string $refs;
    /** @var DiffFile[] */
    public array $files;
    /** @var IntegrationLink[] */
    public array       $integrationLinks = [];
    public ?CodeReview $review           = null;

    /**
     * @param DiffFile[] $files
     */
    public function __construct(
        Repository $repository,
        string $parentHash,
        string $commitHash,
        Author $author,
        Carbon $date,
        string $subject,
        string $body,
        ?string $refs,
        array $files
    ) {
        $this->repository   = $repository;
        $this->parentHash   = $parentHash;
        $this->commitHashes = [$commitHash];
        $this->author       = $author;
        $this->date         = $date;
        $this->subject      = $subject;
        $this->body         = $body;
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
