<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use RuntimeException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class CodeReviewFileService
{
    public function __construct(
        private readonly CacheInterface $revisionCache,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly FileTreeGenerator $treeGenerator,
        private readonly DiffFinder $diffFinder,
        private readonly ?Stopwatch $stopwatch = null,
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

        // generate small diff for common usage
        $this->stopwatch?->start('review.files', 'review');
        $reducedFiles = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions, new FileDiffOptions(0, true));
        $this->stopwatch?->stop('review.files');

        $this->stopwatch?->start('review.file-tree', 'review');
        $fileTree = $this->revisionCache->get($cacheKey, function () use ($review, $revisions, $reducedFiles, $cacheKey): DirectoryTreeNode {
            // generate full size files for diff
            $files = $this->diffService->getDiffFiles(Assert::notNull($review->getRepository()), $revisions, new FileDiffOptions(9999999));
            // add full size diff files to cache
            foreach ($files as $diffFile) {
                $this->revisionCache->get($this->getDiffFileCacheKey($cacheKey, $diffFile), static fn() => $diffFile);
            }

            // generate file tree
            return $this->treeGenerator->generate($reducedFiles)
                ->flatten()
                ->sort(static fn(DiffFile $left, DiffFile $right) => strcmp($left->getFilename(), $right->getFilename()));
        });
        $this->stopwatch?->stop('review.file-tree');

        // get selected file (if any)
        $this->stopwatch?->start('review.find.file', 'review');
        $selectedFile = $this->diffFinder->findFileByPath($reducedFiles, $filePath);
        $this->stopwatch?->stop('review.find.file');

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
