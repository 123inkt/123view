<?php
declare(strict_types=1);

namespace DR\Review\Entity\Revision;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Revision\RevisionVisibilityRepository;

#[ORM\Entity(repositoryClass: RevisionVisibilityRepository::class)]
#[ORM\Index(name: 'review_user_idx', columns: ['review_id', 'user_id'])]
class RevisionVisibility
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Revision::class, cascade: ['persist'])]
    private Revision $revision;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'])]
    private CodeReview $review;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private User $user;

    #[ORM\Column(nullable: false)]
    private bool $visible = true;

    public function getRevision(): Revision
    {
        return $this->revision;
    }

    public function setRevision(Revision $revision): self
    {
        $this->revision = $revision;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }
}
