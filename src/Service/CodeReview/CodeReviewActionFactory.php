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
        if (preg_match('/^([a-z-]+):(.*)$/', (string)$request->query->get('action'), $matches) !== 1) {
            return null;
        }
        $action = (string)$matches[1];
        $value  = (string)$matches[2];

        return match ($action) {
            'add-comment'  => new AddCommentAction(LineReference::fromString($request->query->get('filePath') . ':' . $value)),
            'add-reply'    => new AddCommentReplyAction($this->commentRepository->find((int)$value)),
            'edit-comment' => new EditCommentAction($this->commentRepository->find((int)$value)),
            'edit-reply'   => new EditCommentReplyAction($this->replyRepository->find((int)$value)),
            default        => null,
        };
    }
}
