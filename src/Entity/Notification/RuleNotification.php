<?php
declare(strict_types=1);

namespace DR\Review\Entity\Notification;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Config\RuleNotificationRepository;

#[ORM\Entity(RuleNotificationRepository::class)]
#[ORM\Index(['rule_id'], name: 'IDX_RULE_ID')]
class RuleNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private Rule $rule;

    #[ORM\Column('`read`', options: ['default' => false])]
    private bool $read = false;

    #[ORM\Column]
    private int $notifyTimestamp;

    #[ORM\Column]
    private int $createTimestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function setRule(Rule $rule): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function setRead(bool $read): self
    {
        $this->read = $read;

        return $this;
    }

    public function getNotifyTimestamp(): int
    {
        return $this->notifyTimestamp;
    }

    public function setNotifyTimestamp(int $notifyTimestamp): self
    {
        $this->notifyTimestamp = $notifyTimestamp;

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
}
