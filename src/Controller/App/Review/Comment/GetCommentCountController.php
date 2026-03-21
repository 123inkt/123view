<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetCommentCountController extends AbstractController
{
    #[Route('app/reviews/{id<\d+>}/comment-count', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] CodeReview $review): JsonResponse
    {
        $data = ['total' => 0, 'open' => 0, 'resolved' => 0];

        foreach ($review->getComments() as $comment) {
            ++$data['total'];
            if ($comment->getState() === CommentStateType::RESOLVED) {
                ++$data['resolved'];
            } else {
                ++$data['open'];
            }
        }

        return new JsonResponse($data);
    }
}
