<?php
declare(strict_types=1);

namespace DR\Review\Entity\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RuleRepository::class)]
class Rule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rules')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $active = false;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private string $name;

    /** @phpstan-var Collection<int, Repository> */
    #[ORM\ManyToMany(targetEntity: Repository::class)]
    private Collection $repositories;

    /** @phpstan-var Collection<int, Recipient> */
    #[ORM\OneToMany(targetEntity: Recipient::class, mappedBy: 'rule', cascade: ['persist', 'remove'])]
    private Collection $recipients;

    /** @phpstan-var Collection<int, Filter> */
    #[ORM\OneToMany(targetEntity: Filter::class, mappedBy: 'rule', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $filters;

    /** @phpstan-var Collection<int, RuleNotification> */
    #[ORM\OneToMany(targetEntity: RuleNotification::class, mappedBy: 'rule', cascade: ['persist', 'remove'])]
    private Collection $notifications;

    #[ORM\OneToOne(targetEntity: RuleOptions::class, mappedBy: 'rule', cascade: ['persist', 'remove'])]
    private ?RuleOptions $ruleOptions;

    public function __construct()
    {
        $this->repositories  = new ArrayCollection();
        $this->recipients    = new ArrayCollection();
        $this->filters       = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
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
        $this->recipients->removeElement($recipient);

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
        $this->filters->removeElement($filter);

        return $this;
    }

    /**
     * @param Collection<int, RuleNotification> $notifications
     */
    public function setRuleNotifications(Collection $notifications): self
    {
        $this->notifications = $notifications;

        return $this;
    }

    /**
     * @return Collection<int, RuleNotification>
     */
    public function getRuleNotifications(): Collection
    {
        return $this->notifications;
    }

    public function getRuleOptions(): ?RuleOptions
    {
        return $this->ruleOptions;
    }

    public function setRuleOptions(RuleOptions $ruleOptions): self
    {
        // set the owning side of the relation
        $ruleOptions->setRule($this);
        $this->ruleOptions = $ruleOptions;

        return $this;
    }
}
