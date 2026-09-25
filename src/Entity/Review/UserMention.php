<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Review\UserMentionRepository;

#[ORM\Entity(repositoryClass: UserMentionRepository::class)]
class UserMention
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Comment::class, cascade: ['persist'], inversedBy: 'mentions')]
    private Comment $comment;

    #[ORM\Id]
    #[ORM\Column(nullable: false)]
    private int $userId;

    public function getComment(): Comment
    {
        return $this->comment;
    }

    public function setComment(Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
