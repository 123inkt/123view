<?php
declare(strict_types=1);

namespace DR\Review\Entity\Ai;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Ai\AiReviewReferenceSectionRepository;

/**
 * A single, self-contained piece of review guidance belonging to an {@see AiReviewReference}.
 * Content is capped at MAX_CONTENT_LENGTH characters so a single tool call can never
 * exhaust a large portion of the model's context window.
 */
#[ORM\Entity(repositoryClass: AiReviewReferenceSectionRepository::class)]
#[ORM\Index(name: 'IDX_AI_REVIEW_REFERENCE_SECTION_REFERENCE', columns: ['reference_id'])]
class AiReviewReferenceSection
{
    public const int MAX_CONTENT_LENGTH = 30_000;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AiReviewReference::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(name: 'reference_id', nullable: false)]
    private AiReviewReference $reference;

    #[ORM\Column(type: 'string', length: 255)]
    private string $heading;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sortOrder = 0;

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

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
