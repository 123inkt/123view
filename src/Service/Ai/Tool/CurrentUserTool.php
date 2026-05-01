<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Service\User\UserEntityProvider;
use Mcp\Capability\Attribute\McpTool;

#[McpTool('get-current-user', 'Returns the id, name and email of the currently authenticated user.')]
class CurrentUserTool
{
    public function __construct(private readonly UserEntityProvider $userEntityProvider)
    {
    }

    /**
     * @return array{id: int, name: string, email: string}
     */
    public function __invoke(): array
    {
        $user = $this->userEntityProvider->getCurrentUser();

        return [
            'id'    => $user->getId(),
            'name'  => $user->getName(),
            'email' => $user->getEmail(),
        ];
    }
}
