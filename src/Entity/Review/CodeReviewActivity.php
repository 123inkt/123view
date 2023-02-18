<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewActivityRepository;

#[ApiResource(
    operations: [
        new GetCollection()
    ]
)]
#[ORM\Entity(repositoryClass: CodeReviewActivityRepository::class)]
class CodeReviewActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\Column]
    private ?string $eventName = null;

    /** @var array<string, int|float|bool|string|null> */
    #[ORM\Column(type: 'json', nullable: true)]
    private array $data = [];

    #[ORM\Column]
    private ?int $createTimestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(CodeReview $review): self
    {
        $this->review = $review;

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * @return array<string, int|float|bool|string|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getDataValue(string $key): int|float|bool|string|null
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param array<string, int|float|bool|string|null> $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }
}
