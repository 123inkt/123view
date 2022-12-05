<?php
declare(strict_types=1);

namespace DR\Review\Message;

interface CodeReviewAwareInterface
{
    public function getName(): string;

    public function getReviewId(): int;

    /**
     * @return array<string, int|string|bool|float|null>
     */
    public function getPayload(): array;
}
