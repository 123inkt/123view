<?php

declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

readonly class CommentLocationResolver
{
    public function __construct(private CodeReviewDiffService $diffService)
    {
    }

    /**
     * @throws UnprocessableEntityHttpException
     */
    public function resolve(CodeReview $review, string $filepath, int $line): void
    {
        foreach ($this->diffService->getDiff($review) as $diffFile) {
            if ($diffFile->filePathAfter !== $filepath) {
                continue;
            }

            foreach ($diffFile->getBlocks() as $block) {
                foreach ($block->lines as $diffLine) {
                    if ($diffLine->lineNumberAfter === $line) {
                        return;
                    }
                }
            }
        }

        throw new UnprocessableEntityHttpException('The filepath and line must exist on the current diff side.');
    }
}
