<?php
declare(strict_types=1);

namespace DR\Review\Entity\Ai;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;

/**
 * A named, administrator-managed collection of review guidance (sections) that is
 * surfaced to the AI review agent only for files matching one of its matchers.
 */
#[ORM\Entity(repositoryClass: AiReviewReferenceRepository::class)]
class AiReviewReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $priority = 0;

    /** @phpstan-var Collection<int, AiReviewReferenceSection> */
    #[ORM\OneToMany(targetEntity: AiReviewReferenceSection::class, mappedBy: 'reference', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $sections;

    /** @phpstan-var Collection<int, AiReviewReferenceMatcher> */
    #[ORM\OneToMany(targetEntity: AiReviewReferenceMatcher::class, mappedBy: 'reference', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $matchers;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->matchers = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function hasId(): bool
    {
        return isset($this->id);
    }

    public function getId(): int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return Collection<int, AiReviewReferenceSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(AiReviewReferenceSection $section): self
    {
        if ($this->sections->contains($section) === false) {
            $this->sections->add($section);
            $section->setReference($this);
        }

        return $this;
    }

    public function removeSection(AiReviewReferenceSection $section): self
    {
        $this->sections->removeElement($section);

        return $this;
    }

    /**
     * @return Collection<int, AiReviewReferenceMatcher>
     */
    public function getMatchers(): Collection
    {
        return $this->matchers;
    }

    public function addMatcher(AiReviewReferenceMatcher $matcher): self
    {
        if ($this->matchers->contains($matcher) === false) {
            $this->matchers->add($matcher);
            $matcher->setReference($this);
        }

        return $this;
    }

    public function removeMatcher(AiReviewReferenceMatcher $matcher): self
    {
        $this->matchers->removeElement($matcher);

        return $this;
    }
}
