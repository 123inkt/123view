<?php

declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

readonly class CommentLocationResolver
{
    public function __construct(private CodeReviewDiffService $diffService)
    {
    }

    /**
     * @throws UnprocessableEntityHttpException|Throwable
     */
    public function resolve(CodeReview $review, string $filepath, int $line): void
    {
        foreach ($this->diffService->getDiff($review) as $diffFile) {
            // check filename against the "recent" file name and line numbers
            if ($diffFile->filePathAfter === $filepath) {
                foreach ($diffFile->getBlocks() as $block) {
                    foreach ($block->lines as $diffLine) {
                        if ($diffLine->lineNumberAfter === $line) {
                            return;
                        }
                    }
                }
                throw new UnprocessableEntityHttpException('The line must exist on the current diff side.');
            }

            if ($diffFile->isDeleted() && $diffFile->filePathBefore === $filepath) {
                // check filename against the original filename before deletion
                foreach ($diffFile->getBlocks() as $block) {
                    foreach ($block->lines as $diffLine) {
                        if ($diffLine->lineNumberBefore === $line) {
                            return;
                        }
                    }
                }
                throw new UnprocessableEntityHttpException('The line must exist on the deleted diff side.');
            }
        }

        throw new UnprocessableEntityHttpException('The filepath and line must exist on the current diff side.');
    }
}
