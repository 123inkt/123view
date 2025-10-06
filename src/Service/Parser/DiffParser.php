<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DiffParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const PATTERN = '#(?:^|\n)diff --git a/(.*?) b/(.*?)(?:\n|$)#';

    public function __construct(private readonly DiffFileParser $fileParser)
    {
    }

    /**
     * @return DiffFile[]
     * @throws ParseException
     */
    public function parse(string $patch): array
    {
        $files = [];

        preg_match_all(self::PATTERN, $patch, $matches);
        $patchFiles = preg_split(self::PATTERN, $patch);
        // @codeCoverageIgnoreStart
        if ($patchFiles === false) {
            throw new ParseException('Failed to parse patch: ' . $patch);
        }
        // @codeCoverageIgnoreEnd

        foreach ($patchFiles as $index => $patchFile) {
            // skip the first match as it is just newlines
            if ($index === 0) {
                continue;
            }

            $diffFile                 = new DiffFile();
            $diffFile->raw            = $patchFile;
            $diffFile->filePathBefore = $matches[1][$index - 1] ?? null;
            $diffFile->filePathAfter  = $matches[2][$index - 1] ?? null;

            $this->logger?->debug(sprintf('DiffParser: parsing: %s - %s', $diffFile->filePathBefore, $diffFile->filePathAfter));

            $diffFile = $this->fileParser->parse($patchFile, $diffFile);

            // warmup internal counters
            $diffFile->getTotalNrOfLines();

            $files[] = $diffFile;
        }

        return $files;
    }
}
