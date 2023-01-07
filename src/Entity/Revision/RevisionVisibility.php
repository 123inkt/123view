<?php
declare(strict_types=1);

namespace DR\Review\Entity\Revision;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;

#[ORM\Entity(repositoryClass: RevisionVisibilityRepository::class)]
#[ORM\Index(columns: ['review_id', 'user_id'], name: 'review_user_idx')]
class RevisionVisibility
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Revision::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Revision $revision = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: false)]
    private bool $visible = true;

    public function getRevision(): ?Revision
    {
        return $this->revision;
    }

    public function setRevision(?Revision $revision): self
    {
        $this->revision = $revision;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
