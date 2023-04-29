<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Report\CodeInspectionRepository;

#[ORM\Entity(repositoryClass: CodeInspectionRepository::class)]
#[ORM\Index(['commit_hash', 'repository_id', 'file'], name: 'IDX_COMMIT_HASH_REPOSITORY_FILE')]
#[ORM\UniqueConstraint('IDX_COMMIT_HASH_REPOSITORY_ID', ['commit_hash', 'repository_id', 'inspection_id'])]
class CodeInspection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $commitHash = null;

    #[ORM\Column(length: 50)]
    private ?string $inspectionId = null;

    #[ORM\Column(length: 50)]
    private ?string $severity = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column]
    private ?int $lineNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rule = null;

    #[ORM\ManyToOne(targetEntity: Repository::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCommitHash(): ?string
    {
        return $this->commitHash;
    }

    public function setCommitHash(?string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }

    public function getInspectionId(): ?string
    {
        return $this->inspectionId;
    }

    public function setInspectionId(?string $inspectionId): self
    {
        $this->inspectionId = $inspectionId;

        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): self
    {
        $this->severity = $severity;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    public function setLineNumber(int $lineNumber): self
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(?string $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }
}
