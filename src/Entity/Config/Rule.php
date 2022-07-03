<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RuleRepository::class)]
class Rule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = false;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    /** @phpstan-var Collection<int, Repository> */
    #[ORM\ManyToMany(targetEntity: Repository::class)]
    private Collection $repositories;

    /** @phpstan-var Collection<int, Recipient> */
    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: Recipient::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $recipients;

    /** @phpstan-var Collection<int, Filter> */
    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: Filter::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $filters;

    #[ORM\OneToOne(mappedBy: 'rule', targetEntity: RuleOptions::class, cascade: ['persist', 'remove'])]
    private ?RuleOptions $ruleOptions;

    public function __construct()
    {
        $this->repositories = new ArrayCollection();
        $this->recipients   = new ArrayCollection();
        $this->filters      = new ArrayCollection();
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
