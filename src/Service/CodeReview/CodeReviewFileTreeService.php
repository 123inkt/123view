<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeHighlight\HighlightedFileService;
use DR\Review\Service\Git\Diff\DiffFileUpdater;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Utils\Assert;
use Throwable;

class CodeReviewFileTreeService
{
    public function __construct(
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFileUpdater $diffFileUpdater,
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array{0: DirectoryTreeNode<DiffFile>, 1: DiffFile[]}
     * @throws Throwable
     */
    public function getFileTree(CodeReview $review, array $revisions, FileDiffOptions $diffOptions): array
    {
        $repository = Assert::notNull($review->getRepository());

        // generate diff files
        if (count($revisions) === 0) {
            $files = [];
        } elseif ($diffOptions->reviewType === CodeReviewType::BRANCH) {
            $files = $this->diffService->getDiffForBranch($review, $revisions, (string)$review->getReferenceId(), $diffOptions);
        } else {
            $files = $this->diffService->getDiffForRevisions($repository, $revisions, $diffOptions);
        }

        // prune large diff files
        $files = $this->diffFileUpdater->update($files, $diffOptions->visibleLines ?? 6, HighlightedFileService::MAX_LINE_COUNT);

        // generate file tree
        $fileTree = $this->treeGenerator->generate($files)
            ->flatten()
            ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename()));

        return [$fileTree, $files];
    }
}
