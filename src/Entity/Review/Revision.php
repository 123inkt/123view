<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;

#[ORM\Entity(repositoryClass: RevisionRepository::class)]
#[ORM\UniqueConstraint(name: 'repository_commit_hash', columns: ['repository_id', 'commit_hash'])]
#[ORM\Index(columns: ['create_timestamp'], name: 'create_timestamp_idx')]
class Revision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $commitHash = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $authorEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $authorName = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\ManyToOne(targetEntity: Repository::class, cascade: ['persist'], inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'revisions')]
    private ?CodeReview $review = null;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommitHash(): ?string
    {
        return $this->commitHash;
    }

    public function setCommitHash(string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

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

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(?CodeReview $review): void
    {
        $this->review = $review;
    }
}
