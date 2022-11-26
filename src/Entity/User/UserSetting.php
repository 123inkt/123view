<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Doctrine\Type\ColorThemeType;
use DR\GitCommitNotification\Repository\User\UserSettingRepository;

#[ORM\Entity(repositoryClass: UserSettingRepository::class)]
class UserSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: ColorThemeType::TYPE, options: ['default' => ColorThemeType::THEME_AUTO])]
    private string $colorTheme = ColorThemeType::THEME_AUTO;

    #[ORM\Column]
    private bool $mailCommentAdded = true;

    #[ORM\Column]
    private bool $mailCommentResolved = true;

    #[ORM\Column]
    private bool $mailCommentReplied = true;

    #[ORM\OneToOne(inversedBy: 'setting', targetEntity: User::class)]
    private ?User $user = null;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getColorTheme(): string
    {
        return $this->colorTheme;
    }

    public function setColorTheme(string $colorTheme): self
    {
        $this->colorTheme = $colorTheme;

        return $this;
    }

    public function isMailCommentAdded(): bool
    {
        return $this->mailCommentAdded;
    }

    public function setMailCommentAdded(bool $mailCommentAdded): self
    {
        $this->mailCommentAdded = $mailCommentAdded;

        return $this;
    }

    public function isMailCommentResolved(): bool
    {
        return $this->mailCommentResolved;
    }

    public function setMailCommentResolved(bool $mailCommentResolved): self
    {
        $this->mailCommentResolved = $mailCommentResolved;

        return $this;
    }

    public function isMailCommentReplied(): bool
    {
        return $this->mailCommentReplied;
    }

    public function setMailCommentReplied(bool $mailCommentReplied): self
    {
        $this->mailCommentReplied = $mailCommentReplied;

        return $this;
    }
}
