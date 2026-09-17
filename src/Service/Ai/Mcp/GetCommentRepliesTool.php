<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Throwable;

#[McpTool('get_comment_replies', 'Returns all the replies to a specific comment')]
readonly class GetCommentRepliesTool
{
    public function __construct(private CommentRepository $commentRepository)
    {
    }

    /**
     * @return array<int, array{
     *     replyId: int,
     *     commentId: int,
     *     message: string,
     *     author: array{userId: int, name: string, email: string},
     *     createdAt: string
     * }>
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The id of the comment to list replies for', minimum: 1)] int $commentId): array
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }

        return array_map(
            static fn(CommentReply $reply): array => [
                'replyId'   => $reply->getId(),
                'commentId' => $reply->getComment()->getId(),
                'message'   => $reply->getMessage(),
                'author'    => [
                    'userId' => $reply->getUser()->getId(),
                    'name'   => $reply->getUser()->getName(),
                    'email'  => $reply->getUser()->getEmail(),
                ],
                'createdAt' => date('c', $reply->getCreateTimestamp()),
            ],
            $comment->getReplies()->toArray()
        );
    }
}
