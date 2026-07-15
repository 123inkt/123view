<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Util;

use DR\Review\Service\Util\MessageSanitizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MessageSanitizer::class)]
class MessageSanitizerTest extends AbstractTestCase
{
    private MessageSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new MessageSanitizer();
    }

    public function testSanitizeWithNoReplacementsReturnsValueUnchanged(): void
    {
        static::assertSame('some log output', $this->sanitizer->sanitize('some log output', []));
    }

    public function testSanitizeReplacesAllOccurrences(): void
    {
        $replacements = ['secret' => '***', 'password123' => '[REDACTED]'];

        static::assertSame(
            'url: https://***@host/repo, auth: [REDACTED]',
            $this->sanitizer->sanitize('url: https://secret@host/repo, auth: password123', $replacements)
        );
    }

    public function testSanitizeReplacesMultipleOccurrencesOfSameToken(): void
    {
        static::assertSame(
            'a=***, b=***',
            $this->sanitizer->sanitize('a=token, b=token', ['token' => '***'])
        );
    }
}
