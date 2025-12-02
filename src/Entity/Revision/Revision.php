<?php
declare(strict_types=1);

namespace DR\Review\Entity\Revision;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Repository\Revision\RevisionRepository;

#[ORM\Entity(repositoryClass: RevisionRepository::class)]
#[ORM\UniqueConstraint(name: 'repository_commit_hash', columns: ['repository_id', 'commit_hash'])]
#[ORM\Index(name: 'create_timestamp_idx', columns: ['create_timestamp'])]
#[ORM\Index(name: 'first_branch_repository_idx', columns: ['first_branch', 'repository_id'])]
class Revision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $commitHash;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $parentHash = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstBranch;

    #[ORM\Column(length: 255)]
    private string $authorEmail;

    #[ORM\Column(length: 255)]
    private string $authorName;

    #[ORM\Column]
    private int $createTimestamp;

    #[ORM\ManyToOne(targetEntity: Repository::class, cascade: ['persist'], inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'revisions')]
    private ?CodeReview $review = null;

    /** @phpstan-var Collection<int, RevisionFile> */
    #[ORM\OneToMany(targetEntity: RevisionFile::class, mappedBy: 'revision', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getParentHash(): ?string
    {
        return $this->parentHash;
    }

    public function setParentHash(?string $parentHash): self
    {
        $this->parentHash = $parentHash;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFirstBranch(): ?string
    {
        return $this->firstBranch;
    }

    public function setFirstBranch(?string $firstBranch): self
    {
        $this->firstBranch = $firstBranch;

        return $this;
    }

    public function getAuthorEmail(): string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): self
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

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

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(?CodeReview $review): void
    {
        $this->review = $review;
    }

    /**
     * @return Collection<int, RevisionFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    /**
     * @param Collection<int, RevisionFile> $files
     */
    public function setFiles(Collection $files): Revision
    {
        $this->files = $files;

        return $this;
    }
}
