<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;

#[ApiResource(
    operations: [
        new GetCollection()
    ],
    output    : CodeReviewOutput::class,
    order     : ['updateTimestamp' => 'DESC'],
    provider  : CodeReviewProvider::class
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id'            => 'exact',
        'title'         => 'partial',
        'repository.id' => 'exact',
        'state'         => 'exact',
        'reviewerState' => 'exact'
    ]
)]
#[ApiFilter(DateFilter::class, properties: ['createTimestamp', 'updateTimestamp'])]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'title',
        'repository.id',
        'createTimestamp',
        'updateTimestamp'
    ],
    arguments : ['orderParameterName' => 'order']
)]
#[ORM\Entity(repositoryClass: CodeReviewRepository::class)]
#[ORM\Index(['repository_id', 'title'], name: 'IDX_REPOSITORY_TITLE')]
#[ORM\Index(['repository_id', 'state'], name: 'IDX_REPOSITORY_STATE')]
#[ORM\Index(['create_timestamp', 'repository_id'], name: 'IDX_CREATE_TIMESTAMP_REPOSITORY')]
#[ORM\Index(['update_timestamp', 'repository_id'], name: 'IDX_UPDATE_TIMESTAMP_REPOSITORY')]
#[ORM\UniqueConstraint('IDX_REFERENCE_ID_REPOSITORY_ID', ['reference_id', 'repository_id'])]
#[ORM\UniqueConstraint('IDX_REPOSITORY_ID_PROJECT_ID', ['project_id', 'repository_id'])]
class CodeReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Unique key per project to have a incremental sequence per repository instead of a global sequence */
    #[ORM\Column]
    private ?int $projectId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceId = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: CodeReviewStateType::TYPE, options: ["default" => CodeReviewStateType::OPEN])]
    private string $state = CodeReviewStateType::OPEN;

    /** @var int[] */
    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $actors = [];

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column]
    private ?int $updateTimestamp = null;

    #[ORM\ManyToOne(targetEntity: Repository::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: Revision::class, cascade: ['persist'], orphanRemoval: false, indexBy: 'id')]
    #[ORM\OrderBy(["createTimestamp" => "ASC"])]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReviewer> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: CodeReviewer::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $reviewers;

    /** @phpstan-var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'review', targetEntity: Comment::class, cascade: ['persist', 'remove'], orphanRemoval: false, indexBy: 'id')]
    private Collection $comments;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
        $this->reviewers = new ArrayCollection();
        $this->comments  = new ArrayCollection();
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

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function setProjectId(?int $projectId): CodeReview
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function setReferenceId(string $referenceId): CodeReview
    {
        $this->referenceId = $referenceId;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getUpdateTimestamp(): ?int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

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
        return $this->getReviewersState() === CodeReviewerStateType::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->getReviewersState() === CodeReviewerStateType::REJECTED;
    }

    /**
     * Review is rejected when atleast 1 reviewer rejected
     * Review is accepted when _all_ reviewers accepted
     * Review is open in other cases
     */
    public function getReviewersState(): string
    {
        if (count($this->getReviewers()) === 0) {
            return CodeReviewerStateType::OPEN;
        }

        $accepted = true;
        foreach ($this->reviewers as $reviewer) {
            if ($reviewer->getState() !== CodeReviewerStateType::ACCEPTED) {
                $accepted = false;
            }

            if ($reviewer->getState() === CodeReviewerStateType::REJECTED) {
                return CodeReviewerStateType::REJECTED;
            }
        }

        return $accepted ? CodeReviewerStateType::ACCEPTED : CodeReviewerStateType::OPEN;
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
     * @return int[]
     */
    public function getActors(): array
    {
        return $this->actors;
    }

    /**
     * @param int[] $actors
     */
    public function setActors(array $actors): self
    {
        $this->actors = $actors;

        return $this;
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
