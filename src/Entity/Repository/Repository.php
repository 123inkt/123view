<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Doctrine\Type\UriType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Utils\Assert;
use DR\Utils\EquatableInterface;
use League\Uri\Contracts\UriInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Constraint;

#[ApiResource(
    operations          : [new GetCollection(security: 'is_granted("' . Roles::ROLE_USER . '")')],
    normalizationContext: [
        'groups' => ['repository:read'],
    ]
)]
#[ApiFilter(BooleanFilter::class, properties: ['active'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'name', 'createTimestamp'], arguments: ['orderParameterName' => 'order'])]
#[ORM\Entity(repositoryClass: RepositoryRepository::class)]
#[ORM\Index(name: 'active_idx', columns: ['active'])]
class Repository implements EquatableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['repository:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    #[Groups(['repository:read'])]
    private bool $active = true;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Constraint\Regex('/^[a-z][a-z0-9-]*[a-z0-9]$/')]
    #[Constraint\Length(min: 2, max: 255)]
    #[Groups(['repository:read'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Constraint\Length(max: 255)]
    #[Groups(['repository:read'])]
    private string $displayName;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'master'])]
    #[Constraint\Length(max: 255)]
    #[Groups(['repository:read'])]
    private string $mainBranchName = 'master';

    #[ORM\Column(type: UriType::TYPE, length: 255)]
    private UriInterface $url;

    #[ORM\ManyToOne(targetEntity: RepositoryCredential::class)]
    #[JoinColumn(name: 'credential_id', referencedColumnName: 'id')]
    private ?RepositoryCredential $credential = null;

    /** @phpstan-var RepositoryGitType::GITLAB|RepositoryGitType::GITHUB|null */
    #[ORM\Column(type: RepositoryGitType::TYPE, length: 20, nullable: true)]
    private ?string $gitType = null;

    #[ORM\Column]
    #[Groups(['repository:read'])]
    private bool $favorite = false;

    #[ORM\Column(type: 'integer', options: ['default' => 900])]
    #[Constraint\Range(min: 0)]
    private int $updateRevisionsInterval = 900;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updateRevisionsTimestamp = null;

    #[ORM\Column(type: 'integer', options: ['default' => 3600])]
    #[Constraint\Range(min: 0)]
    private int $validateRevisionsInterval = 3600;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $validateRevisionsTimestamp = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['repository:read'])]
    private ?int $createTimestamp = null;

    /** @phpstan-var Collection<string, RepositoryProperty> */
    #[ORM\OneToMany(
        targetEntity : RepositoryProperty::class,
        mappedBy     : 'repository',
        cascade      : ['persist', 'remove'],
        orphanRemoval: true,
        indexBy      : 'name'
    )]
    private Collection $repositoryProperties;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'repository', cascade: ['remove'], orphanRemoval: true)]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReview> */
    #[ORM\OneToMany(targetEntity: CodeReview::class, mappedBy: 'repository', cascade: ['remove'], orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->repositoryProperties = new ArrayCollection();
        $this->revisions            = new ArrayCollection();
        $this->reviews              = new ArrayCollection();
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getMainBranchName(): string
    {
        return $this->mainBranchName;
    }

    public function setMainBranchName(string $mainBranchName): Repository
    {
        $this->mainBranchName = $mainBranchName;

        return $this;
    }

    public function hasUrl(): bool
    {
        return isset($this->url);
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }

    public function setUrl(UriInterface $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCredential(): ?RepositoryCredential
    {
        return $this->credential;
    }

    public function setCredential(?RepositoryCredential $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * @phpstan-return RepositoryGitType::GITLAB|RepositoryGitType::GITHUB|null
     */
    public function getGitType(): ?string
    {
        return $this->gitType;
    }

    /**
     * @phpstan-param RepositoryGitType::GITLAB|RepositoryGitType::GITHUB|null $gitType
     */
    public function setGitType(?string $gitType): self
    {
        $this->gitType = $gitType;

        return $this;
    }

    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    public function setFavorite(bool $favorite): Repository
    {
        $this->favorite = $favorite;

        return $this;
    }

    public function getUpdateRevisionsInterval(): int
    {
        return $this->updateRevisionsInterval;
    }

    public function setUpdateRevisionsInterval(int $updateRevisionsInterval): void
    {
        $this->updateRevisionsInterval = $updateRevisionsInterval;
    }

    public function getUpdateRevisionsTimestamp(): ?int
    {
        return $this->updateRevisionsTimestamp;
    }

    public function setUpdateRevisionsTimestamp(int $updateRevisionsTimestamp): void
    {
        $this->updateRevisionsTimestamp = $updateRevisionsTimestamp;
    }

    public function getValidateRevisionsInterval(): int
    {
        return $this->validateRevisionsInterval;
    }

    public function setValidateRevisionsInterval(int $validateRevisionsInterval): Repository
    {
        $this->validateRevisionsInterval = $validateRevisionsInterval;

        return $this;
    }

    public function getValidateRevisionsTimestamp(): ?int
    {
        return $this->validateRevisionsTimestamp;
    }

    public function setValidateRevisionsTimestamp(?int $validateRevisionsTimestamp): Repository
    {
        $this->validateRevisionsTimestamp = $validateRevisionsTimestamp;

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

    public function getRepositoryProperty(string $name): ?string
    {
        return $this->repositoryProperties->get($name)?->getValue();
    }

    /**
     * @return Collection<string, RepositoryProperty>
     */
    public function getRepositoryProperties(): Collection
    {
        return $this->repositoryProperties;
    }

    public function setRepositoryProperty(RepositoryProperty $repositoryProperty): self
    {
        $currentProperty = $this->repositoryProperties->get(Assert::string($repositoryProperty->getName()));
        if ($currentProperty !== null) {
            $currentProperty->setValue(Assert::string($repositoryProperty->getValue()));
        } else {
            $repositoryProperty->setRepository($this);
            $this->repositoryProperties->set(Assert::string($repositoryProperty->getName()), $repositoryProperty);
        }

        return $this;
    }

    public function removeRepositoryProperty(RepositoryProperty $repositoryProperty): self
    {
        $this->repositoryProperties->remove(Assert::string($repositoryProperty->getName()));

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

    /**
     * @return Collection<int, CodeReview>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * @param Collection<int, CodeReview> $reviews
     */
    public function setReviews(Collection $reviews): self
    {
        $this->reviews = $reviews;

        return $this;
    }

    public function equalsTo(mixed $other): bool
    {
        return $other instanceof self && $this->id === $other->id;
    }
}
