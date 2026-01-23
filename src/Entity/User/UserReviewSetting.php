<?php
declare(strict_types=1);

namespace DR\Review\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Repository\User\UserReviewSettingRepository;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;

#[ORM\Entity(repositoryClass: UserReviewSettingRepository::class)]
class UserReviewSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(options: ['default' => 6])]
    private int $diffVisibleLines = 6;

    #[ORM\Column(length: 50, options: ['default' => 'all'])]
    private string $diffComparisonPolicy = 'all';

    #[ORM\Column(length: 50, options: ['default' => 'inline'])]
    private string $reviewDiffMode = 'inline';

    #[ORM\Column(length: 50, options: ['default' => 'all'])]
    private string $reviewCommentVisibility = 'all';

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'reviewSetting')]
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDiffVisibleLines(): int
    {
        return $this->diffVisibleLines;
    }

    public function setDiffVisibleLines(int $diffVisibleLines): self
    {
        $this->diffVisibleLines = $diffVisibleLines;

        return $this;
    }

    public function getDiffComparisonPolicy(): DiffComparePolicy
    {
        return DiffComparePolicy::tryFrom($this->diffComparisonPolicy) ?? DiffComparePolicy::ALL;
    }

    public function setDiffComparisonPolicy(DiffComparePolicy $diffComparisonPolicy): self
    {
        $this->diffComparisonPolicy = $diffComparisonPolicy->value;

        return $this;
    }

    public function getReviewDiffMode(): ReviewDiffModeEnum
    {
        return ReviewDiffModeEnum::tryFrom($this->reviewDiffMode) ?? ReviewDiffModeEnum::INLINE;
    }

    public function setReviewDiffMode(ReviewDiffModeEnum $reviewDiffMode): self
    {
        $this->reviewDiffMode = $reviewDiffMode->value;

        return $this;
    }

    public function getReviewCommentVisibility(): CommentVisibilityEnum
    {
        return CommentVisibilityEnum::tryFrom($this->reviewCommentVisibility) ?? CommentVisibilityEnum::ALL;
    }

    public function setReviewCommentVisibility(CommentVisibilityEnum $reviewCommentVisibility): self
    {
        $this->reviewCommentVisibility = $reviewCommentVisibility->value;

        return $this;
    }
}
