<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Exception\ParseException;
use DR\Review\Git\LineReader;
use DR\Review\Service\Parser\Unified\UnifiedBlockParser;
use DR\Utils\Assert;
use Throwable;

class DiffFileParser
{
    private const PATTERN = '/^@@ -(\d+)(?:,\d+)? \\+(\d+)(?:,\d+)? @@.*$/m';

    private UnifiedBlockParser $blockParser;

    public function __construct(UnifiedBlockParser $blockParser)
    {
        $this->blockParser = $blockParser;
    }

    /**
     * @throws ParseException
     */
    public function parse(string $patch, DiffFile $fileDiff): DiffFile
    {
        try {
            return $this->tryParse($patch, $fileDiff);
        } catch (Throwable $exception) {
            throw new ParseException(
                sprintf('File: `%s`: %s', $fileDiff->getDirname() . '/' . $fileDiff->getFilename(), $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    public function tryParse(string $patch, DiffFile $fileDiff): DiffFile
    {
        $parts = Assert::notFalse(preg_split(self::PATTERN, $patch));
        preg_match_all(self::PATTERN, $patch, $matches);

        // read the parts of the diff
        foreach ($parts as $index => $part) {
            $lines = LineReader::fromString(trim($part, "\n"));

            // first part is file info
            if ($index <= 0) {
                $fileDiff = $this->readFileInfo($fileDiff, $lines);
            } else {
                $lineNumberBefore = (int)$matches[1][$index - 1];
                $lineNumberAfter  = (int)$matches[2][$index - 1];

                $fileDiff->addBlock($this->blockParser->parse($lineNumberBefore, $lineNumberAfter, $lines));
            }
        }

        return $fileDiff;
    }

    private function readFileInfo(DiffFile $fileDiff, LineReader $lines): DiffFile
    {
        for ($line = $lines->current(); $line !== null; $line = $lines->next()) {
            if (preg_match('/^old mode (\d+)$/', $line, $matches) === 1) {
                $fileDiff->fileModeBefore = $matches[1];
            }

            if (preg_match('/^new mode (\d+)$/', $line, $matches) === 1) {
                $fileDiff->fileModeAfter = $matches[1];
            }

            if (str_starts_with($line, 'new file mode')) {
                $fileDiff->filePathBefore = null;
            }

            if (str_starts_with($line, 'deleted file mode')) {
                $fileDiff->filePathAfter = null;
            }

            if (str_starts_with($line, 'Binary files')) {
                $fileDiff->binary = true;
            }

            if (preg_match('/^index (\w+)\.\.(\w+)/', $line, $matches) !== 1) {
                continue;
            }

            $fileDiff->hashStart = $matches[1];
            $fileDiff->hashEnd   = $matches[2];
        }

        return $fileDiff;
    }
}
