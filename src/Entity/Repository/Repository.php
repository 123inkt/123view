<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\UriType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Config\RepositoryRepository;
use League\Uri\Uri;

#[ORM\Entity(repositoryClass: RepositoryRepository::class)]
#[ORM\Index(columns: ['active'], name: 'active_idx')]
class Repository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $active = true;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'master'])]
    private string $mainBranchName = 'master';

    #[ORM\Column(type: UriType::TYPE, length: 255)]
    private ?Uri $url = null;

    #[ORM\Column]
    private bool $favorite = false;

    #[ORM\Column(type: 'integer', options: ['default' => 900])]
    private ?int $updateRevisionsInterval = 900;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updateRevisionsTimestamp = null;

    #[ORM\Column(type: 'integer', options: ['default' => 3600])]
    private ?int $validateRevisionsInterval = 3600;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $validateRevisionsTimestamp = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createTimestamp = null;

    /** @phpstan-var Collection<int, RepositoryProperty> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryProperty::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $repositoryProperties;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: Revision::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReview> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: CodeReview::class, cascade: ['remove'], orphanRemoval: true)]
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

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
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

    public function getUrl(): ?Uri
    {
        return $this->url;
    }

    public function setUrl(Uri $url): self
    {
        $this->url = $url;

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

    public function getUpdateRevisionsInterval(): ?int
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

    public function getValidateRevisionsInterval(): ?int
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
        /** @var RepositoryProperty $property */
        foreach ($this->repositoryProperties as $property) {
            if ($property->getName() === $name) {
                return $property->getValue();
            }
        }

        return null;
    }

    /**
     * @return Collection<int, RepositoryProperty>
     */
    public function getRepositoryProperties(): Collection
    {
        return $this->repositoryProperties;
    }

    public function addRepositoryProperty(RepositoryProperty $repositoryProperty): self
    {
        $exists = $this->repositoryProperties->exists(
            static fn($key, RepositoryProperty $property) => $repositoryProperty->getName() === $property->getName()
        );
        if ($exists === false) {
            $this->repositoryProperties[] = $repositoryProperty;
            $repositoryProperty->setRepository($this);
        }

        return $this;
    }

    public function removeRepositoryProperty(RepositoryProperty $repositoryProperty): self
    {
        if ($this->repositoryProperties->removeElement($repositoryProperty)) {
            // set the owning side to null (unless already changed)
            if ($repositoryProperty->getRepository() === $this) {
                $repositoryProperty->setRepository(null);
            }
        }

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
}
