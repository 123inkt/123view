<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use DR\GitCommitNotification\Repository\RuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleRepository::class)]
class Rule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    /** @phpstan-var Collection<int, Repository> */
    #[ORM\ManyToMany(targetEntity: Repository::class)]
    private Collection $repositories;

    /** @phpstan-var Collection<int, Recipient> */
    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: Recipient::class, orphanRemoval: true)]
    private Collection $recipients;

    /** @phpstan-var Collection<int, Filter> */
    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: Filter::class, orphanRemoval: true)]
    private Collection $filters;

    /** @phpstan-var Collection<int, ExternalLink> */
    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: ExternalLink::class, orphanRemoval: true)]
    private Collection $externalLinks;

    #[ORM\OneToOne(mappedBy: 'rule', targetEntity: RuleOptions::class, cascade: ['persist', 'remove'])]
    private ?RuleOptions $ruleOptions;

    public function __construct()
    {
        $this->repositories  = new ArrayCollection();
        $this->recipients    = new ArrayCollection();
        $this->filters       = new ArrayCollection();
        $this->externalLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
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

    /**
     * @return Collection<int, Repository>
     */
    public function getRepositories(): Collection
    {
        return $this->repositories;
    }

    public function addRepository(Repository $repository): self
    {
        if (!$this->repositories->contains($repository)) {
            $this->repositories[] = $repository;
        }

        return $this;
    }

    public function removeRepository(Repository $repository): self
    {
        $this->repositories->removeElement($repository);

        return $this;
    }

    /**
     * @return Collection<int, Recipient>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setRule($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            // set the owning side to null (unless already changed)
            if ($recipient->getRule() === $this) {
                $recipient->setRule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Filter>
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(Filter $filter): self
    {
        if (!$this->filters->contains($filter)) {
            $this->filters[] = $filter;
            $filter->setRule($this);
        }

        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        if ($this->filters->removeElement($filter)) {
            // set the owning side to null (unless already changed)
            if ($filter->getRule() === $this) {
                $filter->setRule(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExternalLink>
     */
    public function getExternalLinks(): Collection
    {
        return $this->externalLinks;
    }

    public function addExternalLink(ExternalLink $externalLink): self
    {
        if (!$this->externalLinks->contains($externalLink)) {
            $this->externalLinks[] = $externalLink;
            $externalLink->setRule($this);
        }

        return $this;
    }

    public function removeExternalLink(ExternalLink $externalLink): self
    {
        if ($this->externalLinks->removeElement($externalLink)) {
            // set the owning side to null (unless already changed)
            if ($externalLink->getRule() === $this) {
                $externalLink->setRule(null);
            }
        }

        return $this;
    }

    public function getRuleOptions(): ?RuleOptions
    {
        return $this->ruleOptions;
    }

    public function setRuleOptions(RuleOptions $ruleOptions): self
    {
        // set the owning side of the relation if necessary
        if ($ruleOptions->getRule() !== $this) {
            $ruleOptions->setRule($this);
        }

        $this->ruleOptions = $ruleOptions;

        return $this;
    }
}
