<?php
declare(strict_types=1);

namespace DR\Review\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\User\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password = null;

    /** @var string[]|null */
    #[ORM\Column(type: SpaceSeparatedStringValueType::TYPE, length: 500)]
    private ?array $roles = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserSetting::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private ?UserSetting $setting = null;

    /** @phpstan-var Collection<int, Rule> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rule::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $rules;

    /** @phpstan-var Collection<int, CodeReviewer> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CodeReviewer::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $reviewers;

    /** @phpstan-var Collection<int, Comment> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $comments;

    /** @phpstan-var Collection<int, CommentReply> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CommentReply::class, cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $replies;

    public function __construct()
    {
        $this->rules     = new ArrayCollection();
        $this->reviewers = new ArrayCollection();
        $this->comments  = new ArrayCollection();
        $this->replies   = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getSetting(): UserSetting
    {
        return $this->setting ??= (new UserSetting())->setUser($this);
    }

    public function setSetting(?UserSetting $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * @return Collection<int, Rule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function addRule(Rule $rule): self
    {
        if (!$this->rules->contains($rule)) {
            $this->rules[] = $rule;
            $rule->setUser($this);
        }

        return $this;
    }

    public function removeRule(Rule $rule): self
    {
        if ($this->rules->removeElement($rule)) {
            // set the owning side to null (unless already changed)
            if ($rule->getUser() === $this) {
                $rule->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string[]
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    /**
     * @see UserInterface
     * @codeCoverageIgnore
     */
    public function eraseCredentials(): void
    {
        // no credentials, authentication via SSO only.
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

    /**
     * @return Collection<int, CommentReply>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    /**
     * @param Collection<int, CommentReply> $replies
     */
    public function setReplies(Collection $replies): self
    {
        $this->replies = $replies;

        return $this;
    }
}
