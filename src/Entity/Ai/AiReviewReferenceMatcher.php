<?php
declare(strict_types=1);

namespace DR\Review\Entity\Ai;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Ai\AiReviewReferenceMatcherRepository;

/**
 * Declares which changed files an {@see AiReviewReference} applies to.
 * `filePattern` is a glob pattern matched against the file path (e.g. `*.php`, `src/Entity/**`).
 * `codeMarker` is optional and, when set, additionally requires the substring/regex to be present
 * in the file's diff content (e.g. `extends`) before the reference is considered applicable.
 */
#[ORM\Entity(repositoryClass: AiReviewReferenceMatcherRepository::class)]
#[ORM\Index(name: 'IDX_AI_REVIEW_REFERENCE_MATCHER_REFERENCE', columns: ['reference_id'])]
class AiReviewReferenceMatcher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AiReviewReference::class, inversedBy: 'matchers')]
    #[ORM\JoinColumn(name: 'reference_id', nullable: false)]
    private AiReviewReference $reference;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filePattern;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $codeMarker = null;

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

    public function getReference(): AiReviewReference
    {
        return $this->reference;
    }

    public function setReference(AiReviewReference $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getFilePattern(): string
    {
        return $this->filePattern;
    }

    public function setFilePattern(string $filePattern): self
    {
        $this->filePattern = $filePattern;

        return $this;
    }

    public function getCodeMarker(): ?string
    {
        return $this->codeMarker;
    }

    public function setCodeMarker(?string $codeMarker): self
    {
        $this->codeMarker = $codeMarker;

        return $this;
    }
}
