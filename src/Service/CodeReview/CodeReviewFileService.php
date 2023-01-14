<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\CodeHighlight\HighlightedFileService;
use DR\Review\Service\Git\Diff\DiffFileUpdater;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class CodeReviewFileService
{
    public function __construct(
        private readonly CacheInterface $revisionCache,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFileUpdater $diffFileUpdater,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array{0: DirectoryTreeNode<DiffFile>, 1: ?DiffFile}
     * @throws Throwable
     */
    public function getFiles(CodeReview $review, array $revisions, ?string $filePath): array
    {
        $cacheKey = $this->getReviewCacheKey($review, $revisions);

        /** @var DirectoryTreeNode<DiffFile> $fileTree */
        $fileTree = $this->revisionCache->get($cacheKey, function () use ($review, $revisions, $cacheKey): DirectoryTreeNode {
            // generate diff files
            $files = $this->diffService->getDiffFiles(
                Assert::notNull($review->getRepository()),
                $revisions,
                new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF)
            );

            // prune large diff files
            $files = $this->diffFileUpdater->update($files, 6, HighlightedFileService::MAX_LINE_COUNT);

            // add file diff to cache
            foreach ($files as $diffFile) {
                $this->revisionCache->get($this->getDiffFileCacheKey($cacheKey, $diffFile), static fn() => $diffFile);
            }

            // generate file tree
            return $this->treeGenerator->generate($files)
                ->flatten()
                ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename()));
        });

        // get selected file (if any)
        $selectedFile = $this->diffFinder->findFileByPath($fileTree->getFilesRecursive(), $filePath);

        // get full file from cache
        if ($selectedFile !== null) {
            $selectedFile = $this->revisionCache->get(
                $this->getDiffFileCacheKey($cacheKey, $selectedFile),
                static fn() => throw new RuntimeException('cache missed')
            );
        }

        return [$fileTree, $selectedFile];
    }

    /**
     * @param Revision[] $revisions
     */
    private function getReviewCacheKey(CodeReview $review, array $revisions): string
    {
        return hash('sha512', 'review-file-' . $review->getId() . implode('', array_map(static fn($rev) => $rev->getCommitHash(), $revisions)));
    }

    private function getDiffFileCacheKey(string $cacheKeyPrefix, DiffFile $diffFile): string
    {
        return hash('sha512', sprintf('%s-%s-%s', $cacheKeyPrefix, $diffFile->getPathname(), $diffFile->hashEnd));
    }
}
