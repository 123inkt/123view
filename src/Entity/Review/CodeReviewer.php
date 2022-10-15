<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Review\CodeReviewerRepository;

#[ORM\Entity(repositoryClass: CodeReviewerRepository::class)]
class CodeReviewer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: CodeReviewerStateType::TYPE, options: ['default' => CodeReviewerStateType::OPEN])]
    private ?string $state = CodeReviewerStateType::OPEN;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'reviewers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'reviewers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
