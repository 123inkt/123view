<?php

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

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\ManyToMany(targetEntity: Repository::class)]
    private Collection $repositories;

    #[ORM\OneToMany(mappedBy: 'rule', targetEntity: Recipient::class, orphanRemoval: true)]
    private Collection $recipients;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $diffAlgorithm;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    public function __construct()
    {
        $this->repositories = new ArrayCollection();
        $this->recipients = new ArrayCollection();
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

    public function getDiffAlgorithm(): ?string
    {
        return $this->diffAlgorithm;
    }

    public function setDiffAlgorithm(string $diffAlgorithm): self
    {
        $this->diffAlgorithm = $diffAlgorithm;

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
}
