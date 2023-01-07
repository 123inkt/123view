<?php
declare(strict_types=1);

namespace DR\Review\Entity\Revision;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;

#[ORM\Entity(repositoryClass: RevisionVisibilityRepository::class)]
#[ORM\UniqueConstraint(name: 'review_revision_user', columns: ['review_id', 'revision_id', 'user_id'])]
#[ORM\Index(columns: ['review_id', 'user_id'], name: 'review_user_idx')]
class RevisionVisibility
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Revision::class, cascade: ['persist'])]
    private ?Revision $revision = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'])]
    private ?CodeReview $review = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $visible = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRevision(): ?Revision
    {
        return $this->revision;
    }

    public function setRevision(?Revision $revision): RevisionVisibility
    {
        $this->revision = $revision;

        return $this;
    }

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(?CodeReview $review): RevisionVisibility
    {
        $this->review = $review;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): RevisionVisibility
    {
        $this->user = $user;

        return $this;
    }
}
