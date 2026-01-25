<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Security\Role\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentVisibilityController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('app/reviews/comment-visibility', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(CommentVisibilityRequest $request): JsonResponse
    {
        $visibility = $request->getVisibility();
        $user       = $this->getUser();

        $user->getReviewSetting()->setReviewCommentVisibility($visibility);
        $this->entityManager->flush();

        return new JsonResponse('ok');
    }
}
