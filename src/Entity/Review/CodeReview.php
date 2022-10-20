<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;

#[ORM\Entity(repositoryClass: CodeReviewRepository::class)]
#[ORM\Index(['repository_id', 'title'], name: 'IDX_REPOSITORY_TITLE')]
#[ORM\Index(['repository_id', 'state'], name: 'IDX_REPOSITORY_STATE')]
class CodeReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: CodeReviewStateType::TYPE, options: ["default" => CodeReviewStateType::OPEN])]
    private string $state = CodeReviewStateType::OPEN;

    #[ORM\ManyToOne(targetEntity: Repository::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: Revision::class, cascade: ['persist'], orphanRemoval: false)]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReviewer> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: CodeReviewer::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $reviewers;

    /** @phpstan-var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: Comment::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $comments;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
        $this->reviewers = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function addRevision(Revision $revision): self
    {
        if ($revision->getReview() !== $this) {
            $revision->setReview($this);
        }
        $this->revisions->add($revision);

        return $this;
    }

    /**
     * @return Collection<int, Revision>
     */
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    /**
     * @param Collection<int, Revision> $revisions
     */
    public function setRevisions(Collection $revisions): self
    {
        $this->revisions = $revisions;

        return $this;
    }

    public function isAccepted(): bool
    {
        if (count($this->getReviewers()) === 0) {
            return false;
        }

        foreach ($this->reviewers as $reviewer) {
            if ($reviewer->getState() !== CodeReviewerStateType::ACCEPTED) {
                return false;
            }
        }

        return true;
    }

    public function getReviewer(User $user): ?CodeReviewer
    {
        foreach ($this->reviewers as $reviewer) {
            if ($reviewer->getUser()?->getId() === $user->getId()) {
                return $reviewer;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, CodeReviewer>
     */
    public function getReviewers(): Collection
    {
        return $this->reviewers;
    }

    /**
     * @param Collection<int, CodeReviewer> $reviewers
     */
    public function setReviewers(Collection $reviewers): self
    {
        $this->reviewers = $reviewers;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Collection<int, Comment> $comments
     */
    public function setComments(Collection $comments): self
    {
        $this->comments = $comments;

        return $this;
    }
}
