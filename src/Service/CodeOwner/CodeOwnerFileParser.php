<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Utils\Arrays;

readonly class CodeOwnerFileParser
{
    /**
     * @param non-empty-string $eolCharacter
     */
    public function __construct(
        private CodeOwnerLineParser $lineParser = new CodeOwnerLineParser(),
        private CodeOwnerSectionHeaderParser $sectionHeaderParser = new CodeOwnerSectionHeaderParser(),
        private string $eolCharacter = "\n"
    ) {
    }

    /**
     * @return list<OwnerPattern>
     */
    public function parse(string $content): array
    {
        $lines            = Arrays::explode($this->eolCharacter, $content);
        $patterns         = [];
        $skipSection      = false;
        $sectionDefOwners = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, '[')) {
                [$skipSection, $sectionDefOwners] = $this->sectionHeaderParser->parse($line);
                continue;
            }

            if ($skipSection) {
                continue;
            }

            $pattern = $this->lineParser->parse($line, $sectionDefOwners);
            if ($pattern !== null) {
                $patterns[] = $pattern;
            }
        }

        return $patterns;
    }
}
