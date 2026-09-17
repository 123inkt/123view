<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

#[McpTool('delete_comment_reply', 'Delete a comment reply. Authorization: only allowed to deleted own replies')]
readonly class DeleteCommentReplyTool
{
    public function __construct(private CommentReplyRepository $commentReplyRepository, private Security $security)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The id of the comment reply to deleted', minimum: 1)] int $replyId): string
    {
        $reply = $this->commentReplyRepository->find($replyId);
        if ($reply === null) {
            throw new CommentNotFoundException($replyId);
        }
        if ($this->security->isGranted(CommentReplyVoter::DELETE, $reply) === false) {
            throw new AccessDeniedHttpException();
        }

        $this->commentReplyRepository->remove($reply, true);

        return 'Comment reply deleted';
    }
}
