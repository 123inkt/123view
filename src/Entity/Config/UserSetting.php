<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use DR\GitCommitNotification\Repository\Config\UserSettingRepository;

#[ORM\Entity(repositoryClass: UserSettingRepository::class)]
class UserSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $mailCommentAdded = true;

    #[ORM\Column]
    private bool $mailCommentResolved = true;

    #[ORM\Column]
    private bool $mailCommentReplied = true;

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isMailCommentAdded(): bool
    {
        return $this->mailCommentAdded;
    }

    public function setMailCommentAdded(bool $mailCommentAdded): UserSetting
    {
        $this->mailCommentAdded = $mailCommentAdded;

        return $this;
    }

    public function isMailCommentResolved(): bool
    {
        return $this->mailCommentResolved;
    }

    public function setMailCommentResolved(bool $mailCommentResolved): UserSetting
    {
        $this->mailCommentResolved = $mailCommentResolved;

        return $this;
    }

    public function isMailCommentReplied(): bool
    {
        return $this->mailCommentReplied;
    }

    public function setMailCommentReplied(bool $mailCommentReplied): UserSetting
    {
        $this->mailCommentReplied = $mailCommentReplied;

        return $this;
    }
}
