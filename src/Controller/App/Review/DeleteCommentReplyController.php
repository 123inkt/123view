<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Security\Voter\CommentReplyVoter;
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('reply')]
    public function __invoke(CommentReply $reply): Response
    {
        $this->denyAccessUnlessGranted(CommentReplyVoter::DELETE, $reply);

        $this->replyRepository->remove($reply, true);

        return $this->refererRedirect(ReviewController::class, ['id' => $reply->getComment()?->getReview()?->getId()], ['action']);
    }
}
