<?php
declare(strict_types=1);

namespace DR\Review\Entity\Ai;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Ai\AiReviewConfigurationRepository;

/**
 * Singleton entity holding the globally configurable AI code review instructions.
 * Only a single row (id = SINGLETON_ID) is ever expected to exist.
 */
#[ORM\Entity(repositoryClass: AiReviewConfigurationRepository::class)]
class AiReviewConfiguration
{
    public const int SINGLETON_ID = 1;

    #[ORM\Id]
    #[ORM\Column]
    private int $id = self::SINGLETON_ID;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    #[ORM\Column]
    private int $updateTimestamp = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getUpdateTimestamp(): int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }
}
