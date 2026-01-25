<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Security\Role\Roles;
use DR\Utils\Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ValidateRevisionsController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    #[Route('/app/admin/repository/{id<\d+>}/validate-revisions', self::class, methods: ['GET'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(#[MapEntity] Repository $repository): RedirectResponse
    {
        $this->bus->dispatch(new ValidateRevisionsMessage(Assert::integer($repository->getId())));

        $this->addFlash('success', 'repository.schedule.validate_revisions');

        return $this->redirectToRoute(RepositoriesController::class);
    }
}
