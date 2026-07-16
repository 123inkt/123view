<?php
declare(strict_types=1);

namespace DR\Review\Service\Git;

/**
 * Implemented by command builders that carry sensitive data (e.g. credentials embedded in URLs).
 * The executor applies the redaction pairs to stdout, stderr, log messages, and exception messages
 * before surfacing them outside the process boundary.
 */
interface SensitiveGitCommandBuilderInterface extends GitCommandBuilderInterface
{
    /**
     * Returns search-replacement pairs for redacting sensitive data.
     * Each key is the literal string to replace; the value is the safe substitute.
     *
     * @return array<string, string>
     */
    public function getSensitiveReplacements(): array;
}
