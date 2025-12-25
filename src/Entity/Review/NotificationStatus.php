<?php
declare(strict_types=1);

namespace DR\Review\Entity\Review;

class NotificationStatus
{
    public const STATUS_CREATED  = 1;
    public const STATUS_UPDATED  = 2;
    public const STATUS_RESOLVED = 4;

    public function __construct(private int $status = 0)
    {
    }

    public static function all(): self
    {
        return new self(self::STATUS_CREATED | self::STATUS_UPDATED | self::STATUS_RESOLVED);
    }

    /**
     * @param self::STATUS_* $flag
     */
    public function hasStatus(int $flag): bool
    {
        return ($this->status & $flag) !== 0;
    }

    /**
     * @param self::STATUS_* $flag
     */
    public function addStatus(int $flag): self
    {
        $this->status |= $flag;

        return $this;
    }

    /**
     * @param self::STATUS_* $flag
     */
    public function removeStatus(int $flag): self
    {
        $this->status ^= $flag;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
