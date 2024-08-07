<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Report\CodeCoverageReportRepository;

#[ORM\Entity(repositoryClass: CodeCoverageReportRepository::class)]
#[ORM\Index(name: 'create_timestamp', columns: ['create_timestamp'])]
#[ORM\Index(name: 'repository_create_timestamp', columns: ['repository_id', 'create_timestamp'])]
#[ORM\Index(name: 'repository_commit_hash', columns: ['repository_id', 'commit_hash'])]
class CodeCoverageReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $commitHash;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $branchId = null;

    #[ORM\Column]
    private int $createTimestamp;

    #[ORM\ManyToOne(targetEntity: Repository::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    /** @phpstan-var Collection<int, CodeCoverageFile> */
    #[ORM\OneToMany(targetEntity: CodeCoverageFile::class, mappedBy: 'report', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCommitHash(): string
    {
        return $this->commitHash;
    }

    public function setCommitHash(string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }

    public function getBranchId(): ?string
    {
        return $this->branchId;
    }

    public function setBranchId(?string $branchId): self
    {
        $this->branchId = $branchId;

        return $this;
    }

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return Collection<int, CodeCoverageFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @param Collection<int, CodeCoverageFile> $files
     */
    public function setFiles(Collection $files): self
    {
        $this->files = $files;

        return $this;
    }
}
