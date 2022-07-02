<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Doctrine\Type\FrequencyType;
use DR\GitCommitNotification\Doctrine\Type\MailThemeType;
use DR\GitCommitNotification\Repository\Config\RuleOptionsRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RuleOptionsRepository::class)]
class RuleOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'ruleOptions', targetEntity: Rule::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rule $rule = null;

    #[ORM\Column(type: FrequencyType::TYPE)]
    private string $frequency = FrequencyType::ONCE_PER_HOUR;

    #[ORM\Column(type: DiffAlgorithmType::TYPE)]
    private string $diffAlgorithm = DiffAlgorithmType::MYERS;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $ignoreSpaceAtEol = true;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $ignoreSpaceChange = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $ignoreAllSpace = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $ignoreBlankLines = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $excludeMergeCommits = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $subject = null;

    #[ORM\Column(type: MailThemeType::TYPE)]
    private string $theme = MailThemeType::UPSOURCE;

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

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): self
    {
        $this->frequency = $frequency;

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

    public function isExcludeMergeCommits(): ?bool
    {
        return $this->excludeMergeCommits;
    }

    public function setExcludeMergeCommits(bool $excludeMergeCommits): self
    {
        $this->excludeMergeCommits = $excludeMergeCommits;

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
