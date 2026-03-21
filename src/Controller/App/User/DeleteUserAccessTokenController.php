<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\UserAccessTokenVoter;
use Exception;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteUserAccessTokenController extends AbstractController
{
    public function __construct(private readonly UserAccessTokenRepository $accessTokenRepository)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/app/user/access-token/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] UserAccessToken $token): RedirectResponse
    {
        $this->denyAccessUnlessGranted(UserAccessTokenVoter::DELETE, $token);

        $this->accessTokenRepository->remove($token, true);

        $this->addFlash('success', 'access.token.deletion.success');

        return $this->redirectToRoute(UserAccessTokenController::class);
    }
}
