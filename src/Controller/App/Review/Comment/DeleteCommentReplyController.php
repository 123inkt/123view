<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentReplyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository)
    {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('reply')]
    public function __invoke(?CommentReply $reply): Response
    {
        if ($reply === null) {
            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $this->denyAccessUnlessGranted(CommentReplyVoter::DELETE, $reply);

        $this->replyRepository->remove($reply, true);

        $anchor = 'focus:comment:' . $reply->getComment()?->getId();

        return $this->refererRedirect(ReviewController::class, ['review' => $reply->getComment()?->getReview()], ['action'], $anchor);
    }
}
