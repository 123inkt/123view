<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DiffNumStatParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @return RevisionFile[]
     */
    public function parse(Revision $revision, string $output): array
    {
        $files = [];
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s+(\d+)\s+(.+)$/', trim($line), $matches) !== 1) {
                $this->logger?->notice('DiffNumStatParser: Unable to parse line: {line}', ['line' => $line]);
                continue;
            }
            $files[] = (new RevisionFile())
                ->setRevision($revision)
                ->setLinesAdded((int)$matches[1])
                ->setLinesRemoved((int)$matches[2])
                ->setFilepath($matches[3]);
        }

        return $files;
    }
}
