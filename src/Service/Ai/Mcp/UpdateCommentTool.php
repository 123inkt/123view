<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

#[McpTool(
    'update_comment',
    'Update the contents of a comment. Review id must match the id of the review of the comment id. ' .
    'Authorization: only allowed to updated own replies')]
readonly class UpdateCommentTool
{
    public function __construct(private CommentRepository $commentRepository, private Security $security)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The id of the comment to update', minimum: 1)] int $commentId,
        #[Schema(description: 'The comment text to set, must be valid markdown')] string $message,
    ): string {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }
        if ($this->security->isGranted(CommentVoter::EDIT, $comment) === false) {
            throw new AccessDeniedHttpException();
        }

        $comment->setMessage($message);
        $this->commentRepository->save($comment, true);

        return 'Comment updated';
    }
}
