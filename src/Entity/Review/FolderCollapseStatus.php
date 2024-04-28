<?php

declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;

#[ORM\Entity(repositoryClass: FolderCollapseStatusRepository::class)]
#[ORM\UniqueConstraint('IDX_REVIEW_USER_PATH', ['review_id', 'user_id', 'path'])]
class FolderCollapseStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 500)]
    private string $path;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: CodeReview::class)]
    #[ORM\JoinColumn(nullable: false)]
    private CodeReview $review;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReview(): CodeReview
    {
        return $this->review;
    }

    public function setReview(CodeReview $review): self
    {
        $this->review = $review;

        return $this;
    }
}
