<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Utils\Arrays;
use DR\Utils\Assert;

readonly class CodeOwnerFileParser
{
    /**
     * @param non-empty-string $eolCharacter
     */
    public function __construct(private string $eolCharacter = "\n")
    {
    }

    /**
     * @return list<OwnerPattern>
     */
    public function parse(string $content): array
    {
        $lines = Arrays::explode($this->eolCharacter, $content);

        return Arrays::removeNull(array_map($this->parseLine(...), $lines));
    }

    private function parseLine(string $line): ?OwnerPattern
    {
        $line = trim($line);
        // skip empty line or comment
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        if (preg_match('/^(?P<file_pattern>[^\s]+)\s+(?P<owners>[^#]+)/si', $line, $matches) !== 1) {
            return null;
        }

        /** @var list<string> $owners */
        $owners = Assert::isArray(preg_split('/\s+/', trim($matches['owners'])));

        return new OwnerPattern($matches['file_pattern'], $owners);
    }
}
