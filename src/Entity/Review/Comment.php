<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Review\CommentRepository;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(['review_id', 'file_path'], name: 'IDX_REVIEW_ID_FILE_PATH')]
class Comment
{
    public const STATE_OPEN     = 'open';
    public const STATE_RESOLVED = 'resolved';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 500)]
    private ?string $filePath = null;

    #[ORM\Column(type: 'string', length: 500)]
    private ?string $lineReference = null;

    // todo change to CommentStateType.
    #[ORM\Column(type: 'string', length: 20, options: ['default' => self::STATE_OPEN])]
    private ?string $state = self::STATE_OPEN;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column]
    private ?int $updateTimestamp = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getLineReference(): ?LineReference
    {
        return LineReference::fromString($this->lineReference);
    }

    public function setLineReference(LineReference $lineReference): self
    {
        $this->lineReference = (string)$lineReference;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getUpdateTimestamp(): ?int
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(?int $updateTimestamp): self
    {
        $this->updateTimestamp = $updateTimestamp;

        return $this;
    }

    public function getReview(): ?CodeReview
    {
        return $this->review;
    }

    public function setReview(?CodeReview $review): void
    {
        $this->review = $review;
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
