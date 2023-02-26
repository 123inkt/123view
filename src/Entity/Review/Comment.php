<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(['review_id', 'file_path'], name: 'IDX_REVIEW_ID_FILE_PATH')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 500)]
    private ?string $filePath = null;

    #[ORM\Column(type: 'string', length: 500)]
    private ?string $lineReference = null;

    // todo change to CommentStateType.
    #[ORM\Column(type: 'string', length: 20, options: ['default' => CommentStateType::OPEN])]
    private ?string $state = CommentStateType::OPEN;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\Column]
    private ?int $updateTimestamp = null;

    #[ORM\Column(type: 'type_notification_status', nullable: true)]
    private ?NotificationStatus $notificationStatus = null;

    #[ORM\ManyToOne(targetEntity: CodeReview::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeReview $review = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /** @phpstan-var Collection<int, CommentReply> */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: CommentReply::class, cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: false)]
    private Collection $replies;

    /** @phpstan-var Collection<int, UserMention> */
    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: UserMention::class, cascade: ['persist', 'remove'], fetch: 'LAZY', orphanRemoval: false)]
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
        return $this->lineReference === null ? null : LineReference::fromString($this->lineReference);
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

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
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

    public function getNotificationStatus(): NotificationStatus
    {
        return $this->notificationStatus ??= new NotificationStatus();
    }

    public function setNotificationStatus(?NotificationStatus $notificationStatus): Comment
    {
        $this->notificationStatus = $notificationStatus;

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
