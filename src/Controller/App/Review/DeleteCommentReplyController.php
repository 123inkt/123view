<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository)
    {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('reply')]
    public function __invoke(Request $request, CommentReply $reply): Response
    {
        if ($reply->getUser()?->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $this->replyRepository->remove($reply, true);

        return $this->refererRedirect(ReviewController::class, ['id' => $reply->getComment()?->getReview()?->getId()], ['editComment']);
    }
}
