<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;
use DR\GitCommitNotification\Utility\Assert;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\Cache\CacheInterface;

class CacheableHighlightedFileService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly CacheInterface $revisionCache, private readonly HighlightedFileService $fileService)
    {
    }

    /**
     * @throws Exception|InvalidArgumentException
     */
    public function fromDiffFile(Repository $repository, DiffFile $diffFile): HighlightedFile
    {
        $filePath = $diffFile->getPathname();
        $hashes   = $diffFile->hashStart . $diffFile->hashEnd;

        $key = hash(
            'sha256',
            sprintf('highlight:fromDiffFile:%d-%s-%s', Assert::notNull($repository->getId()), $filePath, $hashes)
        );

        return $this->revisionCache->get($key, fn() => $this->fileService->fromDiffFile($diffFile));
    }
}
