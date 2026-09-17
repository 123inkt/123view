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
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The id of the comment to list replies for', minimum: 1)] int $commentId): array
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }

        return array_map(
            static function (CommentReply $reply) {
                return [
                    'replyId'   => $reply->getId(),
                    'commentId' => $reply->getComment()->getId(),
                    'message'   => $reply->getMessage(),
                    'state'     => $reply->getState(),
                    'author'    => [
                        'userId' => $reply->getUser()->getId(),
                        'name'   => $reply->getUser()->getName(),
                        'email'  => $reply->getUser()->getEmail(),
                    ],
                    'createdAt' => date('c', $reply->getCreateTimestamp()),
                ];
            },
            $comment->getReplies()->toArray()
        );
    }
}
