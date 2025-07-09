<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Doctrine\Type\CommentTagType;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Index(name: 'IDX_REVIEW_ID_FILE_PATH', columns: ['review_id', 'file_path'])]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('comment:read')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 500)]
    #[Groups('comment:read')]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 2000)]
    #[Groups('comment:read')]
    private string $lineReference;

    // todo change to CommentStateType.
    #[ORM\Column(type: 'string', length: 20, options: ['default' => CommentStateType::OPEN])]
    #[Groups('comment:read')]
    private string $state = CommentStateType::OPEN;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $extReferenceId = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('comment:read')]
    private string $message;

    #[ORM\Column(type: CommentTagType::TYPE, nullable: true, enumType: CommentTagEnum::class)]
    #[Groups('comment:read')]
    private ?CommentTagEnum $tag;

    #[ORM\Column]
    #[Groups('comment:read')]
    private int $createTimestamp;

    #[ORM\Column]
    #[Groups('comment:read')]
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
    #[ORM\OneToMany(targetEntity: CommentReply::class, mappedBy: 'comment', cascade: ['persist', 'remove'], fetch: 'EAGER', orphanRemoval: false)]
    private Collection $replies;

    /** @phpstan-var Collection<int, UserMention> */
    #[ORM\OneToMany(targetEntity: UserMention::class, mappedBy: 'comment', cascade: ['persist', 'remove'], fetch: 'LAZY', orphanRemoval: false)]
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

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
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
