<?php

namespace DR\GitCommitNotification\Entity;

use DR\GitCommitNotification\Repository\RuleOptionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RuleOptionsRepository::class)]
class RuleOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(inversedBy: 'ruleOptions', targetEntity: Rule::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $rule;

    #[ORM\Column(type: 'string', length: 255)]
    private $diffAlgorithm;

    #[ORM\Column(type: 'boolean')]
    private $ignoreSpaceAtEol;

    #[ORM\Column(type: 'boolean')]
    private $ignoreSpaceChange;

    #[ORM\Column(type: 'boolean')]
    private $ignoreAllSpace;

    #[ORM\Column(type: 'boolean')]
    private $ignoreBlankLines;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $subject;

    #[ORM\Column(type: 'string', length: 255)]
    private $theme;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setRule(Rule $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function getDiffAlgorithm(): ?string
    {
        return $this->diffAlgorithm;
    }

    public function setDiffAlgorithm(string $diffAlgorithm): self
    {
        $this->diffAlgorithm = $diffAlgorithm;

        return $this;
    }

    public function isIgnoreSpaceAtEol(): ?bool
    {
        return $this->ignoreSpaceAtEol;
    }

    public function setIgnoreSpaceAtEol(bool $ignoreSpaceAtEol): self
    {
        $this->ignoreSpaceAtEol = $ignoreSpaceAtEol;

        return $this;
    }

    public function isIgnoreSpaceChange(): ?bool
    {
        return $this->ignoreSpaceChange;
    }

    public function setIgnoreSpaceChange(bool $ignoreSpaceChange): self
    {
        $this->ignoreSpaceChange = $ignoreSpaceChange;

        return $this;
    }

    public function isIgnoreAllSpace(): ?bool
    {
        return $this->ignoreAllSpace;
    }

    public function setIgnoreAllSpace(bool $ignoreAllSpace): self
    {
        $this->ignoreAllSpace = $ignoreAllSpace;

        return $this;
    }

    public function isIgnoreBlankLines(): ?bool
    {
        return $this->ignoreBlankLines;
    }

    public function setIgnoreBlankLines(bool $ignoreBlankLines): self
    {
        $this->ignoreBlankLines = $ignoreBlankLines;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }
}
