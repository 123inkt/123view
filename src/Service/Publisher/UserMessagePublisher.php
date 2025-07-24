<?php
declare(strict_types=1);

namespace DR\Review\Service\Publisher;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMessagePublisher
{
    public function __construct(private readonly HubInterface $hub, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @throws JsonException
     */
    public function publishTo(string $message, int $userId): void
    {
        $message = $this->translator->trans($message);

        // publish to mercure
        $this->hub->publish(new Update(sprintf('/user/%d', $userId), Json::encode(['topic' => 'message', 'message' => $message]), true));
    }
}
