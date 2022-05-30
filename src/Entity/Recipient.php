<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\RecipientRepository;

#[ORM\Entity(repositoryClass: RecipientRepository::class)]
#[ORM\UniqueConstraint(name: 'rule_email', columns: ['rule_id', 'email'])]
class Recipient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email;

    #[ORM\ManyToOne(targetEntity: Rule::class, cascade: ['persist', 'remove'], inversedBy: 'recipients')]
    #[ORM\JoinColumn(nullable: false)]
    private $rule;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setRule(?Rule $rule): self
    {
        $this->rule = $rule;

        return $this;
    }
}
