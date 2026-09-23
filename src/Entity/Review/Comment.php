<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Input\CreateCommentInput;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\ApiPlatform\Provider\CommentCollectionProvider;
use DR\Review\ApiPlatform\Provider\CommentProvider;
use DR\Review\ApiPlatform\StateProcessor\CreateCommentProcessor;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Doctrine\Type\CommentTagType;
use DR\Review\Doctrine\Type\CommentTypeType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Get(
    uriTemplate : '/comments/{id}',
    requirements: ['id' => '\d+'],
    security    : 'is_granted("' . Roles::ROLE_USER . '")',
    output      : CommentOutput::class,
    provider    : CommentProvider::class,
)]
#[GetCollection(
    paginationEnabled           : true,
    paginationClientEnabled     : false,
    paginationClientItemsPerPage: true,
    order                       : ['createTimestamp' => 'ASC', 'id' => 'ASC'],
    security                    : 'is_granted("' . Roles::ROLE_USER . '")',
    output                      : CommentOutput::class,
    provider                    : CommentCollectionProvider::class,
    parameters                  : [
        'user.id'                => new QueryParameter(filter: new ExactFilter(), property: 'user.id'),
        'review.id'              => new QueryParameter(filter: new ExactFilter(), property: 'review.id'),
        'exact[filepath]'        => new QueryParameter(filter: new ExactFilter(), property: 'filePath'),
        'order[id]'              => new QueryParameter(filter: new SortFilter(), property: 'id'),
        'order[user.id]'         => new QueryParameter(filter: new SortFilter(), property: 'user.id'),
        'order[review.id]'       => new QueryParameter(filter: new SortFilter(), property: 'review.id'),
        'order[filepath]'        => new QueryParameter(filter: new SortFilter(), property: 'filePath'),
        'order[state]'           => new QueryParameter(filter: new SortFilter(), property: 'state'),
        'order[createTimestamp]' => new QueryParameter(filter: new SortFilter(), property: 'createTimestamp'),
        'order[updateTimestamp]' => new QueryParameter(filter: new SortFilter(), property: 'updateTimestamp'),
    ],
)]
#[Post(
    uriTemplate                 : '/code-reviews/{reviewId}/comments',
    uriVariables                : ['reviewId' => new Link(fromClass: CodeReview::class, identifiers: ['id'])],
    requirements                : ['reviewId' => '\\d+'],
    status                      : 201,
    exceptionToStatus           : [SerializerExceptionInterface::class => 422],
    denormalizationContext      : [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false],
    collectDenormalizationErrors: true,
    security                    : 'is_granted("' . Roles::ROLE_USER . '")',
    input                       : CreateCommentInput::class,
    output                      : CommentOutput::class,
    read                        : false,
    processor                   : CreateCommentProcessor::class,
)]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(name: 'IDX_REVIEW_ID_FILE_PATH', columns: ['review_id', 'file_path'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string', length: 500)]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 2000)]
    private string $lineReference;

    #[ORM\Column(type: CommentStateType::TYPE, enumType: CommentStateEnum::class, options: ['default' => CommentStateEnum::Open->value])]
    private CommentStateEnum $state = CommentStateEnum::Open;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $extReferenceId = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: CommentTagType::TYPE, nullable: true, enumType: CommentTagEnum::class)]
    private ?CommentTagEnum $tag;

    #[ORM\Column(type: CommentTypeType::TYPE, enumType: CommentTypeEnum::class, options: ['default' => 'final'])]
    private CommentTypeEnum $type = CommentTypeEnum::Final;

    #[ORM\Column]
    private int $createTimestamp;

    #[ORM\Column]
    private int $updateTimestamp;

    #[ORM\Column(type: 'type_notification_status', nullable: true)]
    private ?NotificationStatus $notificationStatus = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private CodeReview $review;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /** @phpstan-var Collection<int, CommentReply> */
    #[ORM\OneToMany(targetEntity: CommentReply::class, mappedBy: 'comment', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $replies;

    /** @phpstan-var Collection<int, UserMention> */
    #[ORM\OneToMany(targetEntity: UserMention::class, mappedBy: 'comment', cascade: ['persist', 'remove'], orphanRemoval: false)]
    private Collection $mentions;

    public function __construct()
    {
        $this->replies  = new ArrayCollection();
        $this->mentions = new ArrayCollection();
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getLineReference(): LineReference
    {
        return LineReference::fromString($this->lineReference);
    }

    public function setLineReference(LineReference $lineReference): self
    {
        $this->lineReference = (string)$lineReference;

        return $this;
    }

    public function getState(): CommentStateEnum
    {
        return $this->state;
    }

    public function setState(CommentStateEnum $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getExtReferenceId(): ?string
    {
        return $this->extReferenceId;
    }

    public function setExtReferenceId(?string $extReferenceId): self
    {
        $this->extReferenceId = $extReferenceId;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getTag(): ?CommentTagEnum
    {
        return $this->tag;
    }

    public function setTag(?CommentTagEnum $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getType(): CommentTypeEnum
    {
        return $this->type;
    }

    public function setType(CommentTypeEnum $type): self
    {
        $this->type = $type;

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

    public function getUpdateTimestamp(): int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }

    public function getNotificationStatus(): NotificationStatus
    {
        return $this->notificationStatus ??= new NotificationStatus();
    }

    public function setNotificationStatus(?NotificationStatus $notificationStatus): Comment
    {
        $this->notificationStatus = $notificationStatus;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CommentReply>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    /**
     * @param Collection<int, CommentReply> $replies
     */
    public function setReplies(Collection $replies): self
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * @return Collection<int, UserMention>
     */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    /**
     * @param Collection<int, UserMention> $mentions
     */
    public function setMentions(Collection $mentions): self
    {
        $this->mentions = $mentions;

        return $this;
    }
}
