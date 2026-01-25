<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Credentials;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\EditCredentialFormType;
use DR\Review\Message\Revision\RepositoryUpdatedMessage;
use DR\Review\Repository\Config\RepositoryCredentialRepository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditCredentialViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialController extends AbstractController
{
    public function __construct(
        private readonly RepositoryCredentialRepository $credentialRepository,
        private readonly RepositoryRepository $repositoryRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @return array<string, EditCredentialViewModel>|RedirectResponse
     */
    #[Route('/app/admin/credential/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/edit_credential.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, #[MapEntity] ?RepositoryCredential $credential): array|RedirectResponse
    {
        if ($credential === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Credential not found');
        }

        $credential ??= (new RepositoryCredential())->setAuthType(AuthenticationType::BASIC_AUTH);

        $form = $this->createForm(EditCredentialFormType::class, ['credential' => $credential]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editCredentialModel' => new EditCredentialViewModel($credential, $form->createView())];
        }

        $this->credentialRepository->save($credential, true);
        foreach ($this->repositoryRepository->findBy(['credential' => $credential]) as $repository) {
            $this->bus->dispatch(new RepositoryUpdatedMessage((int)$repository->getId()));
        }

        $this->addFlash('success', 'credential.successful.saved');

        return $this->redirectToRoute(CredentialsController::class);
    }
}
