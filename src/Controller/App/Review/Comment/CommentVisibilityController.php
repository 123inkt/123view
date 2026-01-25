<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\User\UserReviewSettingRepository;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentVisibilityController extends AbstractController
{
    public function __construct(private readonly UserReviewSettingRepository $repository)
    {
    }

    #[Route('app/reviews/comment-visibility', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(CommentVisibilityRequest $request): JsonResponse
    {
        $reviewSetting = $this->getUser()->getReviewSetting();

        $reviewSetting->setReviewCommentVisibility($request->getVisibility());
        $this->repository->save($reviewSetting, true);

        return new JsonResponse('ok');
    }
}
