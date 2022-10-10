<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RevisionRepository::class)]
#[ORM\UniqueConstraint(name: 'repository_commit_hash', columns: ['repository_id', 'commit_hash'])]
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
    private ?string $authorEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $authorName = null;

    #[ORM\ManyToOne(targetEntity: Repository::class, cascade: ['persist', 'remove'], inversedBy: 'revisions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

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

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }
}
