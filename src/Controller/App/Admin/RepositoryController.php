<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Exception\RepositoryException;
use DR\Review\Form\Repository\EditRepositoryFormType;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Remote\GitRemoteService;
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
    public function __construct(private readonly RepositoryRepository $repositoryRepository, private readonly GitRemoteService $gitRemoteService)
    {
    }

    /**
     * @return array<string, EditRepositoryViewModel>|RedirectResponse
     * @throws RepositoryException
     */
    #[Route('/app/admin/repository/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/edit_repository.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, #[MapEntity] ?Repository $repository): array|RedirectResponse
    {
        if ($repository === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Repository not found');
        }

        $repository ??= (new Repository())->setCreateTimestamp(time());

        $form = $this->createForm(EditRepositoryFormType::class, ['repository' => $repository]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editRepositoryModel' => new EditRepositoryViewModel($repository, $form->createView())];
        }

        $this->repositoryRepository->save($repository, true);
        $this->gitRemoteService->updateRemoteUrl($repository);

        $this->addFlash('success', 'repository.successful.saved');

        return $this->redirectToRoute(RepositoriesController::class);
    }
}
