<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;

#[ORM\Entity(repositoryClass: RepositoryRepository::class)]
class Repository
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $url = null;

    /** @phpstan-var Collection<int, RepositoryProperty> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: RepositoryProperty::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $repositoryProperties;

    /** @phpstan-var Collection<int, Revision> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: Revision::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $revisions;

    /** @phpstan-var Collection<int, CodeReview> */
    #[ORM\OneToMany(mappedBy: 'repository', targetEntity: CodeReview::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->repositoryProperties = new ArrayCollection();
        $this->revisions            = new ArrayCollection();
        $this->reviews              = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

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
        return $this->revisions;
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
