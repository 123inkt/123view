<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\Entity\User\User;

class UserOutputFactory
{
    public function create(User $user): UserOutput
    {
        return new UserOutput((int)$user->getId(), $user->getName(), $user->getEmail());
    }
}
