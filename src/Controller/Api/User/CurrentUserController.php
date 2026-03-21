<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api\User;

use DR\Review\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CurrentUserController extends AbstractController
{
    #[Route('/api/users/me', name: self::class, methods: 'GET')]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse(
            [
                'id'    => $user->getId(),
                'name'  => $user->getName(),
                'email' => $user->getEmail()
            ]
        );
    }
}
