<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Review\FileSeenStatusRepository;

#[ORM\Entity(repositoryClass: FileSeenStatusRepository::class)]
#[ORM\UniqueConstraint('IDX_REVIEW_USER_FILEPATH', ['review_id', 'user_id', 'file_path'])]
class FileSeenStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $filePath = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class)]
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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(?CodeReview $review): self
    {
        $this->review = $review;

        return $this;
    }
}
