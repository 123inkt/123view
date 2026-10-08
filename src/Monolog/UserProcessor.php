<?php
declare(strict_types=1);

namespace DR\Review\Monolog;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class UserProcessor implements ProcessorInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof UserInterface) {
            return $record;
        }

        $record->extra['user_id'] = $user->getUserIdentifier();

        return $record;
    }
}
