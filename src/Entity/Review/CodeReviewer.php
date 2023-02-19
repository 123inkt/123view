<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewerRepository;

#[ORM\Entity(repositoryClass: CodeReviewerRepository::class)]
class CodeReviewer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: CodeReviewerStateType::TYPE, options: ['default' => CodeReviewerStateType::OPEN])]
    private ?string $state = CodeReviewerStateType::OPEN;

    #[ORM\Column(type: 'integer')]
    private ?int $stateTimestamp = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'reviewers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'reviewers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

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
        if ($this->state !== $state) {
            $this->state = $state;
            $this->setStateTimestamp(time());
        }

        return $this;
    }

    public function getStateTimestamp(): ?int
    {
        return $this->stateTimestamp;
    }

    public function setStateTimestamp(?int $stateTimestamp): void
    {
        $this->stateTimestamp = $stateTimestamp;
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

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
