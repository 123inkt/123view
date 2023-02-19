<?php
declare(strict_types=1);

namespace DR\Review\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\User\UserAccessTokenRepository;

#[ORM\Entity(repositoryClass: UserAccessTokenRepository::class)]
class UserAccessToken
{
    #[ORM\Id]
    #[ORM\Column(length: 80)]
    private ?string $identifier = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column]
    private ?int $useTimestamp = null;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

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
