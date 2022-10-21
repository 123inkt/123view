<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentReplyRepository::class)]
class CommentReply
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column]
    private ?int $updateTimestamp = null;

    #[ORM\ManyToOne(targetEntity: Comment::class, cascade: ['persist'], inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Comment $comment = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): void
    {
        $this->createTimestamp = $createTimestamp;
    }

    public function getUpdateTimestamp(): ?int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?int $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): void
    {
        $this->comment = $comment;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
