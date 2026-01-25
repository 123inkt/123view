<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\SessionKeys;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentVisibilityController extends AbstractController
{
    #[Route('app/reviews/comment-visibility', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(CommentVisibilityRequest $request): JsonResponse
    {
        $visibility = $request->getVisibility();

        $request->getRequest()->getSession()->set(SessionKeys::REVIEW_COMMENT_VISIBILITY->value, $visibility->value);

        return new JsonResponse('ok');
    }
}
