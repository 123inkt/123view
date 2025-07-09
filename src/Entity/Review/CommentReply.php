<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\CommentTagType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentReplyRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommentReplyRepository::class)]
class CommentReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('comment-reply:read')]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('comment-reply:read')]
    private string $message;

    #[ORM\Column(type: CommentTagType::TYPE, nullable: true, enumType: CommentTagEnum::class)]
    #[Groups('comment-reply:read')]
    private ?CommentTagEnum $tag;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $extReferenceId = null;

    #[ORM\Column]
    #[Groups('comment-reply:read')]
    private int $createTimestamp;

    #[ORM\Column]
    #[Groups('comment-reply:read')]
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

    public function getId(): ?int
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
