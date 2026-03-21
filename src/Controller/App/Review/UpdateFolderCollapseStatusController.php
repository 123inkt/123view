<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class UpdateFolderCollapseStatusController extends AbstractController
{
    public function __construct(private readonly FolderCollapseStatusRepository $folderCollapseRepository)
    {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/folder-collapse-status', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): Response
    {
        $state = $request->request->getString('state');
        $path  = $request->request->getString('path');
        if (in_array($state, ['collapsed', 'expanded'], true) === false) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        // register collapse state
        if ($state === 'collapsed') {
            $status = (new FolderCollapseStatus())->setReview($review)->setUser($this->getUser())->setPath($path);
            $this->folderCollapseRepository->save($status);
        } else {
            $this->folderCollapseRepository->removeOneBy(['user' => $this->getUser(), 'review' => $review, 'path' => $path], flush: true);
        }

        return new Response(status: Response::HTTP_ACCEPTED);
    }
}
