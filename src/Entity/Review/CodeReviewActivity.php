<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
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
use Symfony\Component\Serializer\Annotation\Groups;

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
        'review.id'     => 'exact',
        'review.title'  => 'partial',
        'review.state'  => 'exact',
        'repository.id' => 'exact',
        'eventName'     => 'exact',
    ]
)]
#[ApiFilter(RangeFilter::class, properties: ['createTimestamp'])]
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
#[ORM\Index(name: 'IDX_CREATE_TIMESTAMP_USER_EVENT', columns: ['create_timestamp', 'user_id', 'event_name'])]
#[ORM\Index(name: 'IDX_REVIEW_ID', columns: ['review_id'])]
#[ORM\Index(name: 'IDX_EVENT_REPOSITORY', columns: ['event_name'])]
#[ORM\Index(name: 'IDX_USER_ID', columns: ['user_id'])]
class CodeReviewActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review-activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['review-activity:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['code-review:read'])]
    private CodeReview $review;

    #[ORM\Column]
    #[Groups(['review-activity:read'])]
    private string $eventName;

    /** @var array<string, int|float|bool|string|null> */
    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['review-activity:read'])]
    private array $data = [];

    #[ORM\Column]
    #[Groups(['review-activity:read'])]
    private int $createTimestamp;

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

    public function getReview(): CodeReview
    {
        return $this->review;
    }

    public function setReview(CodeReview $review): self
    {
        $this->review = $review;

        return $this;
    }

    public function getEventName(): string
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

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }
}
