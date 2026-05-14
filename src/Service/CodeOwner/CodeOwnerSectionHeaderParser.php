<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeOwner;

use DR\Utils\Assert;

readonly class CodeOwnerSectionHeaderParser
{
    /**
     * Parse a section header line like `[Block Name]` or `[Block Name] @owner1 @owner2`.
     *
     * @return list<string>
     */
    public function parse(string $line): array
    {
        if (preg_match('/^\[[^]]+](?:\s+(?P<owners>[^#]+))?/si', $line, $matches) !== 1) {
            return [];
        }

        $ownersStr = trim($matches['owners'] ?? '');
        if ($ownersStr === '') {
            return [];
        }

        /** @phpstan-var list<string> */
        return Assert::isArray(preg_split('/\s+/', $ownersStr));
    }
}
