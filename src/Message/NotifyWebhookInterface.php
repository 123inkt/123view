<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

interface NotifyWebhookInterface
{
    public function getName(): string;

    /**
     * @return array<string, int|string|bool|float|null>
     */
    public function getPayload(): array;
}
