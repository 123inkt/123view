<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\EditRepositoryFormType;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditRepositoryViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RepositoryController extends AbstractController
{
    public function __construct(private RepositoryRepository $repositoryRepository)
    {
    }

    /**
     * @return array<string, EditRepositoryViewModel>|RedirectResponse
     */
    #[Route('/app/admin/repository/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/edit_repository.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, #[MapEntity] ?Repository $repository): array|RedirectResponse
    {
        if ($repository === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Repository not found');
        }

        $repository ??= new Repository();

        $form = $this->createForm(EditRepositoryFormType::class, ['repository' => $repository]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editRepositoryModel' => new EditRepositoryViewModel($form->createView())];
        }

        $this->repositoryRepository->save($repository, true);

        $this->addFlash('success', 'repository.successful.saved');

        return $this->redirectToRoute(RepositoriesController::class);
    }
}
