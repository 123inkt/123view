<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\User;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserMentionSuggestController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/app/user/mentions', self::class, methods: ['GET'])]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('rule')]
    public function __invoke(Request $request): JsonResponse
    {
        // search users
        $users = $this->userRepository->findBySearchQuery($request->query->get('search', ''), 15);

        // create json array form user objects
        $json = array_map(static fn(User $user) => ['id' => $user->getId(), 'name' => $user->getName()], $users);

        $response = new JsonResponse($json);
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
