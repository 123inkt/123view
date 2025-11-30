<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Service\Git\Review\FileDiffOptions;
use RuntimeException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Throwable;

class CodeReviewFileService
{
    public function __construct(
        private readonly CacheInterface&AdapterInterface $revisionCache,
        private readonly DiffFinder $diffFinder,
        private readonly CodeReviewFileTreeService $fileTreeService
    ) {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array{0: DirectoryTreeNode<DiffFile>, 1: ?DiffFile}
     * @throws Throwable
     */
    public function getFiles(CodeReview $review, array $revisions, ?string $filePath, FileDiffOptions $diffOptions): array
    {
        $cacheKey = $this->getReviewCacheKey($review, $revisions, $diffOptions);

        $cacheItem = $this->revisionCache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            /** @var DirectoryTreeNode<DiffFile> $fileTree */
            $fileTree = $cacheItem->get();
            $files    = $fileTree->getFilesRecursive();
        } else {
            // generate diff files tree
            [$fileTree, $files] = $this->fileTreeService->getFileTree($review, $revisions, $diffOptions);

            // add tree to cache
            $this->revisionCache->get($cacheKey, static fn() => $fileTree);

            // add file diff to cache
            foreach ($files as $diffFile) {
                $this->revisionCache->get($this->getDiffFileCacheKey($cacheKey, $diffFile, $diffOptions), static fn() => $diffFile);
            }
        }

        // get selected file (if any)
        $selectedFile = $this->diffFinder->findFileByPath($files, $filePath);

        // get full file from cache
        if ($selectedFile !== null) {
            $selectedFile = $this->revisionCache->get(
                $this->getDiffFileCacheKey($cacheKey, $selectedFile, $diffOptions),
                static fn() => throw new RuntimeException('cache missed')
            );
        }

        return [$fileTree, $selectedFile];
    }

    /**
     * @param Revision[] $revisions
     */
    private function getReviewCacheKey(CodeReview $review, array $revisions, FileDiffOptions $options): string
    {
        $hashes = array_map(static fn($rev) => $rev->getCommitHash(), $revisions);

        return hash('sha512', sprintf('review-file-%d-%s-%s', $review->getId(), implode('', $hashes), $options));
    }

    private function getDiffFileCacheKey(string $cacheKeyPrefix, DiffFile $diffFile, FileDiffOptions $options): string
    {
        return hash('sha512', sprintf('%s-%s-%s-%s', $cacheKeyPrefix, $diffFile->getPathname(), $diffFile->hashEnd, $options));
    }
}
