<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Utility\Assert;
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('comment')]
    public function __invoke(?Comment $comment): Response
    {
        if ($comment === null) {
            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $this->commentRepository->remove($comment, true);

        $anchor = 'focus:line:' . Assert::notNull($comment->getLineReference())->lineAfter;

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], $anchor);
    }
}
