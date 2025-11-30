<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\ApiPlatform\StateProcessor\CodeReviewProcessor;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\PropertyChangeTrait;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            order   : ['updateTimestamp' => 'DESC'],
            security: 'is_granted("' . Roles::ROLE_USER . '")',
            output  : CodeReviewOutput::class,
            provider: CodeReviewProvider::class
        ),
        new Patch(
            normalizationContext  : ['groups' => ['code_review_write']],
            denormalizationContext: ['groups' => ['code_review_write']],
            security              : 'is_granted("' . Roles::ROLE_USER . '")',
            processor             : CodeReviewProcessor::class
        )
    ]
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
#[ApiFilter(RangeFilter::class, properties: ['createTimestamp', 'updateTimestamp'])]
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
#[ORM\Index(name: 'IDX_TITLE', columns: ['title'])]
#[ORM\Index(name: 'IDX_REPOSITORY_TITLE', columns: ['repository_id', 'title'])]
#[ORM\Index(name: 'IDX_REPOSITORY_STATE', columns: ['repository_id', 'state'])]
#[ORM\Index(name: 'IDX_CREATE_TIMESTAMP_REPOSITORY', columns: ['create_timestamp', 'repository_id'])]
#[ORM\Index(name: 'IDX_UPDATE_TIMESTAMP_REPOSITORY', columns: ['update_timestamp', 'repository_id'])]
#[ORM\UniqueConstraint('IDX_REFERENCE_ID_REPOSITORY_ID', ['reference_id', 'repository_id'])]
#[ORM\UniqueConstraint('IDX_REPOSITORY_ID_PROJECT_ID', ['project_id', 'repository_id'])]
class CodeReview
{
    use PropertyChangeTrait;

    public const PROP_STATE = 'state';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /** Unique key per project to have a incremental sequence per repository instead of a global sequence */
    #[ORM\Column]
    private int $projectId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceId = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $description;

    /** @var CodeReviewType::COMMITS|CodeReviewType::BRANCH */
    #[ORM\Column(type: CodeReviewType::TYPE, options: ["default" => CodeReviewType::COMMITS])]
    private string $type = CodeReviewType::COMMITS;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $targetBranch = null;

    #[ORM\Column(type: CodeReviewStateType::TYPE, options: ["default" => CodeReviewStateType::OPEN])]
    #[Groups(['code_review_write'])]
    private string $state = CodeReviewStateType::OPEN;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $extReferenceId = null;

    #[ORM\Column]
    private bool $aiReviewRequested = false;

    /** @var int[] */
    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $actors = [];

    #[ORM\Column]
    private int $createTimestamp;

    #[ORM\Column]
    private int $updateTimestamp;

    #[ORM\ManyToOne(targetEntity: Repository::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private Repository $repository;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'review', cascade: ['persist'], orphanRemoval: false, indexBy: 'id')]
    #[ORM\OrderBy(["createTimestamp" => "ASC"])]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReviewer> */
    #[ORM\OneToMany(targetEntity: CodeReviewer::class, mappedBy: 'review', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $reviewers;

    /** @phpstan-var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'review', cascade: ['persist', 'remove'], orphanRemoval: false, indexBy: 'id')]
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

    public function hasId(): bool
    {
        return isset($this->id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function setProjectId(int $projectId): self
    {
        $this->projectId = $projectId;

        return $this;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function setReferenceId(?string $referenceId): self
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return CodeReviewType::COMMITS|CodeReviewType::BRANCH
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param CodeReviewType::COMMITS|CodeReviewType::BRANCH $type
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTargetBranch(): ?string
    {
        return $this->targetBranch;
    }

    /**
     * The target branch if the type=BranchReview
     */
    public function setTargetBranch(?string $targetBranch): self
    {
        $this->targetBranch = $targetBranch;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $this->propertyChange(self::PROP_STATE, $this->state, $state);

        return $this;
    }

    public function getExtReferenceId(): ?string
    {
        return $this->extReferenceId;
    }

    public function setExtReferenceId(?string $extReferenceId): self
    {
        $this->extReferenceId = $extReferenceId;

        return $this;
    }

    public function isAiReviewRequested(): bool
    {
        return $this->aiReviewRequested;
    }

    public function setAiReviewRequested(bool $aiReviewRequested): self
    {
        $this->aiReviewRequested = $aiReviewRequested;

        return $this;
    }

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getUpdateTimestamp(): int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function setRepository(Repository $repository): self
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

    public function getReviewer(User $user): ?CodeReviewer
    {
        foreach ($this->reviewers as $reviewer) {
            if ($reviewer->getUser()->getId() === $user->getId()) {
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
