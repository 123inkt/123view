<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentAction;
use DR\GitCommitNotification\Model\Review\Action\AddCommentReplyAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentAction;
use DR\GitCommitNotification\Model\Review\Action\EditCommentReplyAction;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use Symfony\Component\HttpFoundation\Request;

class CodeReviewActionFactory
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly CommentReplyRepository $replyRepository)
    {
    }

    public function createFromRequest(Request $request): ?AbstractReviewAction
    {
        if ($request->query->has('addComment')) {
            return new AddCommentAction(LineReference::fromString($request->query->get('filePath') . ':' . $request->query->get('addComment', '')));
        }

        if ($request->query->has('replyComment')) {
            return new AddCommentReplyAction($this->commentRepository->find($request->query->getInt('replyComment')));
        }

        if ($request->query->has('editComment')) {
            return new EditCommentAction($this->commentRepository->find($request->query->getInt('editComment')));
        }

        if ($request->query->has('editReply')) {
            return new EditCommentReplyAction($this->replyRepository->find($request->query->getInt('editReply')));
        }

        return null;
    }
}
