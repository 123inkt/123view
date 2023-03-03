<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Utility\Assert;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableHighlightedFileService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private readonly AdapterInterface&CacheInterface $revisionCache;

    public function __construct(CacheInterface $revisionCache, private readonly HighlightedFileService $fileService)
    {
        $this->revisionCache = Assert::instanceOf(AdapterInterface::class, $revisionCache);
    }

    /**
     * @throws Exception|InvalidArgumentException
     */
    public function fromDiffFile(Repository $repository, DiffFile $diffFile): ?HighlightedFile
    {
        $filePath = $diffFile->getPathname();
        $hashes   = $diffFile->hashStart . $diffFile->hashEnd;

        $key = hash(
            'sha256',
            sprintf('highlight:fromDiffFile:%d-%s-%s', Assert::notNull($repository->getId()), $filePath, $hashes)
        );

        // cache hit
        $cacheItem = $this->revisionCache->getItem($key);
        if ($cacheItem->isHit() && is_array($cacheItem->get())) {
            return new HighlightedFile($diffFile->getPathname(), static fn() => $cacheItem->get());
        }

        // cache miss
        $highlightedFile = $this->fileService->fromDiffFile($diffFile);
        if ($highlightedFile === null) {
            return null;
        }

        return new HighlightedFile($diffFile->getPathname(), fn() => $this->revisionCache->get($key, $highlightedFile->closure));
    }
}
