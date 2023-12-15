<?php

declare(strict_types=1);

namespace DR\Review\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Repository\User\GitAccessTokenRepository;

#[ORM\Entity(repositoryClass: GitAccessTokenRepository::class)]
#[ORM\UniqueConstraint(name: 'user_tokens', columns: ['user_id', 'git_type'])]
class GitAccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /** @phpstan-var RepositoryGitType::GITLAB|RepositoryGitType::GITHUB */
    #[ORM\Column(type: RepositoryGitType::TYPE, length: 20)]
    private ?string $gitType = null;

    #[ORM\Column]
    private string $token;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    /**
     * @phpstan-return RepositoryGitType::GITLAB|RepositoryGitType::GITHUB
     */
    public function getGitType(): string
    {
        return $this->gitType;
    }

    /**
     * @phpstan-param RepositoryGitType::GITLAB|RepositoryGitType::GITHUB $gitType
     */
    public function setGitType(string $gitType): self
    {
        $this->gitType = $gitType;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
