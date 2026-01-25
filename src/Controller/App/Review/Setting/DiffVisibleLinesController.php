<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Setting;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Review\Setting\DiffVisibleLinesRequest;
use DR\Review\Security\Role\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DiffVisibleLinesController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('app/reviews/setting/diff-visible-lines', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(DiffVisibleLinesRequest $request): RedirectResponse
    {
        $this->getUser()->getReviewSetting()->setDiffVisibleLines($request->getVisibleLines());
        $this->entityManager->flush();

        return $this->refererRedirect('/');
    }
}
