<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Review\Revision;
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
    public function getHighlightedFile(Revision $revision, string $filePath): HighlightedFile
    {
        $key = hash(
            'sha256',
            sprintf('highlight:%d-%s-%s', Assert::notNull($revision->getRepository())->getId(), $revision->getCommitHash(), $filePath)
        );

        return $this->revisionCache->get($key, fn() => $this->fileService->getHighlightedFile($revision, $filePath));
    }
}
