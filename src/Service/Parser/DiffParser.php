<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Parser;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Exception\ParseException;
use Psr\Log\LoggerInterface;

class DiffParser
{
    private const PATTERN = '#(?:^|\n)diff --git a/(.*?) b/(.*?)(?:\n|$)#';

    private LoggerInterface $log;
    private DiffFileParser  $fileParser;

    public function __construct(LoggerInterface $log, DiffFileParser $fileParser)
    {
        $this->log        = $log;
        $this->fileParser = $fileParser;
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
            $diffFile->filePathBefore = $matches[1][$index - 1] ?? null;
            $diffFile->filePathAfter  = $matches[2][$index - 1] ?? null;

            $this->log->debug(sprintf('DiffParser: parsing: %s - %s', $diffFile->filePathBefore, $diffFile->filePathAfter));

            $files[] = $this->fileParser->parse($patchFile, $diffFile);
        }

        return $files;
    }
}
