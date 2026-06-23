<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use Throwable;

readonly class LineReferenceFactory
{
    public function __construct(private CodeReviewDiffService $diffService, private DiffFinder $diffFinder)
    {
    }

    /**
     * @throws Throwable
     */
    public function createFromReview(CodeReview $review, string $filepath, int $lineNumber, string $headSha): LineReference
    {
        $diffFiles = $this->diffService->getDiff($review);
        $diffFile  = $this->diffFinder->findFileByPath($diffFiles, $filepath);

        if ($diffFile === null) {
            return new LineReference($filepath, $filepath, $lineNumber, 0, $lineNumber, $headSha);
        }

        return $this->createFromDiffFile($diffFile, $lineNumber, $headSha);
    }

    public function createFromDiffFile(DiffFile $diffFile, int $lineNumber, string $headSha): LineReference
    {
        $anchorLine    = $lineNumber;
        $offset        = 0;
        $state         = LineReferenceStateEnum::Unknown;
        $currentAnchor = $lineNumber;
        $currentOffset = 0;

        foreach ($diffFile->getBlocks() as $block) {
            foreach ($block->lines as $diffLine) {
                if ($diffLine->lineNumberBefore !== null) {
                    $currentAnchor = $diffLine->lineNumberBefore;
                    $currentOffset = 0;
                } else {
                    $currentOffset++;
                }

                if ($diffLine->lineNumberAfter === $lineNumber) {
                    $anchorLine = $currentAnchor;
                    $offset     = $currentOffset;
                    $state      = match ($diffLine->state) {
                        DiffLine::STATE_ADDED                            => LineReferenceStateEnum::Added,
                        DiffLine::STATE_CHANGED, DiffLine::STATE_INLINED => LineReferenceStateEnum::Modified,
                        DiffLine::STATE_UNCHANGED                        => LineReferenceStateEnum::Unmodified,
                        default                                          => LineReferenceStateEnum::Unknown,
                    };
                    break 2;
                }
            }
        }

        return new LineReference($diffFile->filePathBefore, $diffFile->filePathAfter, $anchorLine, $offset, $lineNumber, $headSha, $state);
    }
}
