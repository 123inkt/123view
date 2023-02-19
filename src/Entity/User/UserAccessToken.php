<?php
declare(strict_types=1);

namespace DR\Review\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\User\UserAccessTokenRepository;

#[ORM\Entity(repositoryClass: UserAccessTokenRepository::class)]
#[ORM\Index(['user_id'], name: 'IDX_USER_ID')]
#[ORM\UniqueConstraint('IDX_TOKEN', ['token'])]
class UserAccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 80, options: ['fixed' => true])]
    private ?string $token = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $usages = 0;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column(nullable: true)]
    private ?int $useTimestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): UserAccessToken
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): UserAccessToken
    {
        $this->user = $user;

        return $this;
    }

    public function getUsages(): int
    {
        return $this->usages;
    }

    public function setUsages(int $usages): UserAccessToken
    {
        $this->usages = $usages;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): UserAccessToken
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getUseTimestamp(): ?int
    {
        return $this->useTimestamp;
    }

    public function setUseTimestamp(?int $useTimestamp): UserAccessToken
    {
        $this->useTimestamp = $useTimestamp;

        return $this;
    }
}
