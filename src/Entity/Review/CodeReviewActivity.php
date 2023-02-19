<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Output\CodeReviewActivityOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewActivityProvider;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Security\Role\Roles;

#[ApiResource(
    operations: [new GetCollection(security: 'is_granted("' . Roles::ROLE_USER . '")')],
    output    : CodeReviewActivityOutput::class,
    order     : ['createTimestamp' => 'DESC'],
    provider  : CodeReviewActivityProvider::class
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id'            => 'exact',
        'user.id'       => 'exact',
        'repository.id' => 'exact',
        'eventName'     => 'exact',
    ]
)]
#[ApiFilter(DateFilter::class, properties: ['createTimestamp'])]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'user.id',
        'repository.id',
        'eventName',
        'createTimestamp',
    ],
    arguments : ['orderParameterName' => 'order']
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
