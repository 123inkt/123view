<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(Request $request, Comment $comment): Response
    {
        if ($comment->getUser()?->getId() !== $this->getUser()?->getId()) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $this->commentRepository->remove($comment, true);

        return $this->refererRedirect(ReviewController::class, ['id' => $comment->getReview()?->getId()]);
    }
}
