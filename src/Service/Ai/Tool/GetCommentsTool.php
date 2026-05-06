<?php

declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use InvalidArgumentException;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;

#[McpTool('get_comments_for_reviews', 'Get all comments on the given review')]
readonly class GetCommentsTool
{
    public function __construct(private CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @return array<array{
     *     commentId: int,
     *     message: string,
     *     state: string,
     *     file: string|null,
     *     line: int,
     *     createdAt: string,
     *     author: array{
     *         userId: int,
     *         name: string,
     *         email: non-empty-string
     *     },
     * }>
     */
    public function __invoke(#[Schema(description: 'The CODE_REVIEW_ID of the code review')] int $codeReviewId): array
    {
        $review = $this->reviewRepository->find($codeReviewId);
        if ($review === null) {
            throw new InvalidArgumentException('Code review not found');
        }

        return array_map(
            static function (Comment $comment) {
                $lineReference = $comment->getLineReference();

                return [
                    'commentId' => (int)$comment->getId(),
                    'message'   => $comment->getMessage(),
                    'state'     => $comment->getState(),
                    'file'      => $lineReference->newPath ?? $lineReference->oldPath,
                    'line'      => $lineReference->lineAfter,
                    'author'    => [
                        'userId' => $comment->getUser()->getId(),
                        'name'   => $comment->getUser()->getName(),
                        'email'  => $comment->getUser()->getEmail(),
                    ],
                    'createdAt' => date('c', $comment->getCreateTimestamp()),
                ];
            },
            $review->getComments()
        );
    }
}
