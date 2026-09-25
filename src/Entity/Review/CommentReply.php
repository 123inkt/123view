<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\SortFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\ApiPlatform\Input\CreateCommentReplyInput;
use DR\Review\ApiPlatform\Input\UpdateCommentReplyInput;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\ApiPlatform\Provider\CommentReplyCollectionProvider;
use DR\Review\ApiPlatform\Provider\CommentReplyProvider;
use DR\Review\ApiPlatform\StateProcessor\CreateCommentReplyProcessor;
use DR\Review\ApiPlatform\StateProcessor\UpdateCommentReplyProcessor;
use DR\Review\Doctrine\Type\CommentTagType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Get(
    uriTemplate : '/comment-replies/{id}',
    requirements : ['id' => '\\d+'],
    security    : 'is_granted("' . Roles::ROLE_USER . '")',
    output      : CommentReplyOutput::class,
    provider    : CommentReplyProvider::class,
)]
#[GetCollection(
    paginationEnabled           : true,
    paginationClientEnabled     : false,
    paginationClientItemsPerPage: true,
    order                       : ['createTimestamp' => 'ASC', 'id' => 'ASC'],
    security                    : 'is_granted("' . Roles::ROLE_USER . '")',
    output                      : CommentReplyOutput::class,
    provider                    : CommentReplyCollectionProvider::class,
    parameters                  : [
        'comment.id'              => new QueryParameter(filter: new ExactFilter(), property: 'comment.id'),
        'order[id]'                => new QueryParameter(filter: new SortFilter(), property: 'id'),
        'order[comment.id]'        => new QueryParameter(filter: new SortFilter(), property: 'comment.id'),
        'order[user.id]'           => new QueryParameter(filter: new SortFilter(), property: 'user.id'),
        'order[createTimestamp]'   => new QueryParameter(filter: new SortFilter(), property: 'createTimestamp'),
        'order[updateTimestamp]'   => new QueryParameter(filter: new SortFilter(), property: 'updateTimestamp'),
    ],
)]
#[Post(
    uriTemplate                 : '/comments/{commentId}/replies',
    uriVariables                : ['commentId' => new Link(fromClass: Comment::class, identifiers: ['id'])],
    requirements                : ['commentId' => '\\d+'],
    status                      : 201,
    exceptionToStatus           : [SerializerExceptionInterface::class => 422],
    denormalizationContext      : [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false],
    collectDenormalizationErrors: true,
    security                    : 'is_granted("' . Roles::ROLE_USER . '")',
    input                       : CreateCommentReplyInput::class,
    output                      : CommentReplyOutput::class,
    read                        : false,
    processor                   : CreateCommentReplyProcessor::class,
)]
#[Patch(
    uriTemplate                 : '/comment-replies/{id}',
    requirements                : ['id' => '\\d+'],
    exceptionToStatus           : [SerializerExceptionInterface::class => 422],
    denormalizationContext      : [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false],
    collectDenormalizationErrors: true,
    security                    : 'is_granted("' . Roles::ROLE_USER . '")',
    input                       : UpdateCommentReplyInput::class,
    output                      : CommentReplyOutput::class,
    read                        : false,
    processor                   : UpdateCommentReplyProcessor::class,
)]
#[ORM\Entity(repositoryClass: CommentReplyRepository::class)]
class CommentReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: CommentTagType::TYPE, nullable: true, enumType: CommentTagEnum::class)]
    private ?CommentTagEnum $tag;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $extReferenceId = null;

    #[ORM\Column]
    private int $createTimestamp;

    #[ORM\Column]
    private int $updateTimestamp;

    #[ORM\Column(type: 'type_notification_status', nullable: true)]
    private ?NotificationStatus $notificationStatus = null;

    #[ORM\ManyToOne(targetEntity: Comment::class, cascade: ['persist'], inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: false)]
    private Comment $comment;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
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

    public function getExtReferenceId(): ?string
    {
        return $this->extReferenceId;
    }

    public function setExtReferenceId(?string $extReferenceId): self
    {
        $this->extReferenceId = $extReferenceId;

        return $this;
    }

    public function getCreateTimestamp(): int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(int $createTimestamp): void
    {
        $this->createTimestamp = $createTimestamp;
    }

    public function getUpdateTimestamp(): int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(int $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getNotificationStatus(): NotificationStatus
    {
        return $this->notificationStatus ??= new NotificationStatus();
    }

    public function setNotificationStatus(?NotificationStatus $notificationStatus): self
    {
        $this->notificationStatus = $notificationStatus;

        return $this;
    }

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function setComment(Comment $comment): void
    {
        $this->comment = $comment;
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
}
