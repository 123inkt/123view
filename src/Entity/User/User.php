<?php
declare(strict_types=1);

namespace DR\Review\Entity\User;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\Controller\Api\User\CurrentUserController;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Utils\ComparableInterface;
use DR\Utils\EquatableInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/users/me',
            routeName  : CurrentUserController::class,
            openapi    : new OpenApiOperation(summary: "Get the current user", description: "Get the current user"),
            security   : 'is_granted("' . Roles::ROLE_USER . '")',
        )
    ],
    output    : UserOutput::class
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'user.email.already.exists')]
#[ORM\UniqueConstraint('EMAIL', ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, ComparableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    /** @var non-empty-string */
    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    /** @var string[]|null */
    #[ORM\Column(type: SpaceSeparatedStringValueType::TYPE, length: 500)]
    private ?array $roles = null;

    #[ORM\Column(nullable: true)]
    private ?int $gitlabUserId = null;

    #[ORM\OneToOne(targetEntity: UserSetting::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private ?UserSetting $setting = null;

    /** @phpstan-var Collection<int, Rule> */
    #[ORM\OneToMany(targetEntity: Rule::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $rules;

    /** @phpstan-var Collection<int, GitAccessToken> */
    #[ORM\OneToMany(targetEntity: GitAccessToken::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $tokens;

    /** @phpstan-var Collection<int, CodeReviewer> */
    #[ORM\OneToMany(targetEntity: CodeReviewer::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $reviewers;

    /** @phpstan-var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $comments;

    /** @phpstan-var Collection<int, CommentReply> */
    #[ORM\OneToMany(targetEntity: CommentReply::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $replies;

    public function __construct()
    {
        $this->rules     = new ArrayCollection();
        $this->tokens    = new ArrayCollection();
        $this->reviewers = new ArrayCollection();
        $this->comments  = new ArrayCollection();
        $this->replies   = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param non-empty-string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getGitlabUserId(): ?int
    {
        return $this->gitlabUserId;
    }

    public function setGitlabUserId(?int $gitlabUserId): self
    {
        $this->gitlabUserId = $gitlabUserId;

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
        $this->rules->removeElement($rule);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
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
     * @return Collection<int, GitAccessToken>
     */
    public function getGitAccessTokens(): Collection
    {
        return $this->tokens;
    }

    /**
     * @param Collection<int, GitAccessToken> $tokens
     */
    public function setGitAccessTokens(Collection $tokens): self
    {
        $this->tokens = $tokens;

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

    public function equalsTo(mixed $other): bool
    {
        return $other instanceof self && $this->id === $other->id;
    }

    public function compareTo(mixed $other): int
    {
        return $other instanceof self === false ? -1 : $this->id <=> $other->id;
    }
}
