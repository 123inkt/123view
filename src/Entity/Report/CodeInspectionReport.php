<?php
declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;

#[ORM\Entity(repositoryClass: CodeInspectionReportRepository::class)]
#[ORM\Index(name: 'create_timestamp', columns: ['create_timestamp'])]
#[ORM\Index(name: 'repository_create_timestamp', columns: ['repository_id', 'create_timestamp'])]
#[ORM\UniqueConstraint('IDX_COMMIT_HASH_REPOSITORY_ID', ['repository_id', 'inspection_id', 'commit_hash'])]
class CodeInspectionReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $commitHash;

    #[ORM\Column(length: 50)]
    private string $inspectionId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $branchId = null;

    #[ORM\ManyToOne(targetEntity: Repository::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    #[ORM\Column]
    private int $createTimestamp;

    /** @phpstan-var Collection<int, CodeInspectionIssue> */
    #[ORM\OneToMany(targetEntity: CodeInspectionIssue::class, mappedBy: 'report', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $issues;

    public function __construct()
    {
        $this->issues = new ArrayCollection();
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

    public function getInspectionId(): string
    {
        return $this->inspectionId;
    }

    public function setInspectionId(string $inspectionId): self
    {
        $this->inspectionId = $inspectionId;

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

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): self
    {
        $this->repository = $repository;

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

    /**
     * @return Collection<int, CodeInspectionIssue>
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    /**
     * @param Collection<int, CodeInspectionIssue> $issues
     */
    public function setIssues(Collection $issues): self
    {
        $this->issues = $issues;

        return $this;
    }
}
