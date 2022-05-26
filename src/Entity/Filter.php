<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use DR\GitCommitNotification\Repository\FilterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilterRepository::class)]
class Filter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'filters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rule $rule;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $type;

    #[ORM\Column(type: 'boolean')]
    private bool $inclusion = false;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $pattern;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isInclusion(): ?bool
    {
        return $this->inclusion;
    }

    public function setInclusion(bool $inclusion): self
    {
        $this->inclusion = $inclusion;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }
}
