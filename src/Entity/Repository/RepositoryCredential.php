<?php
declare(strict_types=1);

namespace DR\Review\Entity\Repository;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Credential\CredentialInterface;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: RepositoryCredentialRepository::class)]
class RepositoryCredential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: AuthenticationType::TYPE, options: ["default" => AuthenticationType::BASIC_AUTH])]
    private string $authType = AuthenticationType::BASIC_AUTH;

    #[ORM\Column(length: 255)]
    private string $value;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
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

    public function getAuthType(): string
    {
        return $this->authType;
    }

    public function setAuthType(string $authType): self
    {
        $this->authType = $authType;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCredentials(): CredentialInterface
    {
        return match ($this->authType) {
            AuthenticationType::BASIC_AUTH => BasicAuthCredential::fromString($this->value),
            default                        => throw new InvalidArgumentException('Unknown auth type: ' . $this->authType),
        };
    }

    public function setCredentials(CredentialInterface $credential): self
    {
        $this->authType = match (true) {
            $credential instanceof BasicAuthCredential => AuthenticationType::BASIC_AUTH,
            default                                    => throw new InvalidArgumentException('Unknown credential type: ' . get_class($credential)),
        };
        $this->value    = (string)$credential;

        return $this;
    }
}
