<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteCredentialController extends AbstractController
{
    public function __construct(private RepositoryCredentialRepository $credentialRepository)
    {
    }

    #[Route('/app/admin/credential/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(#[MapEntity] RepositoryCredential $credential): RedirectResponse
    {
        $this->credentialRepository->remove($credential, true);

        $this->addFlash('success', 'credential.successful.removed');

        return $this->refererRedirect(CredentialsController::class);
    }
}
