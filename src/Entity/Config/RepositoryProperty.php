<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\Config\RepositoryPropertyRepository;

#[ORM\Entity(repositoryClass: RepositoryPropertyRepository::class)]
class RepositoryProperty
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Repository::class, cascade: ['persist', 'remove'], inversedBy: 'repositoryProperties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $value;

    public function __construct(?string $name = null, ?string $value = null)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): self
    {
        $this->repository = $repository;

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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
