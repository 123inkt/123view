<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

#[McpTool('update_comment_reply', 'Update the contents of a comment reply. Authorization: only allowed to updated own replies')]
readonly class UpdateCommentReplyTool
{
    public function __construct(private CommentReplyRepository $commentReplyRepository, private Security $security)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The id of the comment reply to update', minimum: 1)] int $replyId,
        #[Schema(description: 'The comment text to set, must be valid markdown')] string $message,
    ): string {
        $reply = $this->commentReplyRepository->find($replyId);
        if ($reply === null) {
            throw new CommentReplyNotFoundException($replyId);
        }
        if ($this->security->isGranted(CommentReplyVoter::EDIT, $reply) === false) {
            throw new AccessDeniedHttpException();
        }

        $reply->setMessage($message);
        $this->commentReplyRepository->save($reply, true);

        return 'Comment reply updated';
    }
}
