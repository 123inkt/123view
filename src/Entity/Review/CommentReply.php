<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentReplyRepository;

#[ORM\Entity(repositoryClass: CommentReplyRepository::class)]
class CommentReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

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

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
