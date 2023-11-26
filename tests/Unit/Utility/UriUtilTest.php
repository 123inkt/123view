<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\UriUtil;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UriUtil::class)]
class UriUtilTest extends AbstractTestCase
{
    public function testCredentials(): void
    {
        static::assertSame([null, null], UriUtil::credentials(null));
    }

    public function testCredentialsUriWithoutCredentials(): void
    {
        static::assertSame([null, null], UriUtil::credentials(Uri::new('https://example.com')));
    }

    public function testCredentialsUriWithUsername(): void
    {
        static::assertSame(['shërlock', null], UriUtil::credentials(Uri::new('https://sh%C3%ABrlock@example.com')));
    }

    public function testCredentialsUriWithCredentials(): void
    {
        static::assertSame(['sherlock', 'passw*rd'], UriUtil::credentials(Uri::new('https://sherlock:passw%2Ard@example.com')));

        // with special character
        static::assertSame(['sher:lock', 'passw*rd'], UriUtil::credentials(Uri::new('https://sher%3Alock:passw%2Ard@example.com')));
    }
}
