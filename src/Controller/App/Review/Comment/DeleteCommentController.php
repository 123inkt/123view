<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Security\Voter\CommentVoter;
use DR\GitCommitNotification\Utility\Assert;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
