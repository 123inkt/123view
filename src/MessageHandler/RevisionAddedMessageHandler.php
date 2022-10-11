<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\MessageHandler;

use DR\GitCommitNotification\Message\RevisionAddedMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RevisionAddedMessageHandler implements MessageHandlerInterface
{
    public function __construct()
    {
    }

    public function __invoke(RevisionAddedMessage $message)
    {
        // ... do some work - like sending an SMS message!
    }
}
