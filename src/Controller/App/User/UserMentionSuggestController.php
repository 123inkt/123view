<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Request\User\UserMentionSuggestRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserMentionSuggestController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/app/user/mentions', self::class, methods: ['GET'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(UserMentionSuggestRequest $request): JsonResponse
    {
        $preferredUserIds = $request->getPreferredUserIds();

        // search users
        $users = $this->userRepository->findBySearchQuery($request->getSearch(), $preferredUserIds, Roles::ROLE_USER, 15);

        // create json array from user objects
        $json = array_map(static fn(User $user) => ['id' => $user->getId(), 'name' => $user->getName()], $users);

        $response = new JsonResponse($json);
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
