<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity;

use DR\GitCommitNotification\Repository\ExternalLinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExternalLinkRepository::class)]
class ExternalLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $pattern;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $url;

    #[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'externalLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rule $rule;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

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
