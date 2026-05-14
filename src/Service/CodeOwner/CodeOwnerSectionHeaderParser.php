<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Utils\Assert;

readonly class CodeOwnerSectionHeaderParser
{
    /**
     * Parse a section header line like `[Block Name]` or `[Block Name] @owner1 @owner2`.
     * Returns a tuple of [skipSection, defaultOwners]:
     * - skipSection=true when the header has no default owners (block should be ignored)
     * - skipSection=false with the owners list when the header defines default owners
     *
     * @return array{bool, list<string>}
     */
    public function parse(string $line): array
    {
        if (preg_match('/^\[[^]]+](?:\s+(?P<owners>[^#]+))?/si', $line, $matches) !== 1) {
            return [true, []];
        }

        $ownersStr = trim($matches['owners'] ?? '');
        if ($ownersStr === '') {
            return [true, []];
        }

        /** @var list<string> $owners */
        $owners = Assert::isArray(preg_split('/\s+/', $ownersStr));

        return [false, $owners];
    }
}
