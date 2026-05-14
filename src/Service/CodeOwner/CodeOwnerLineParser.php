<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Utils\Assert;

readonly class CodeOwnerLineParser
{
    /**
     * @param list<string> $defaultOwners
     */
    public function parse(string $line, array $defaultOwners = []): ?OwnerPattern
    {
        if (preg_match('/^(?P<file_pattern>[^\s]+)\s+(?P<owners>[^#]+)/si', $line, $matches) !== 1) {
            if ($defaultOwners === []) {
                return null;
            }
            $filePattern = trim(preg_replace('/#.*$/', '', $line) ?? $line);

            return $filePattern !== '' ? new OwnerPattern($filePattern, $defaultOwners) : null;
        }

        /** @var list<string> $owners */
        $owners = Assert::isArray(preg_split('/\s+/', trim($matches['owners'])));

        return new OwnerPattern($matches['file_pattern'], $owners);
    }
}
