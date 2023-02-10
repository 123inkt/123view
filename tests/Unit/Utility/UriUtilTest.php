<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\UriUtil;
use League\Uri\Uri;

/**
 * @coversDefaultClass \DR\Review\Utility\UriUtil
 */
class UriUtilTest extends AbstractTestCase
{
    /**
     * @covers ::credentials
     */
    public function testCredentials(): void
    {
        static::assertSame([null, null], UriUtil::credentials(null));
    }

    /**
     * @covers ::credentials
     */
    public function testCredentialsUriWithoutCredentials(): void
    {
        static::assertSame([null, null], UriUtil::credentials(Uri::createFromString('https://example.com')));
    }

    /**
     * @covers ::credentials
     */
    public function testCredentialsUriWithUsername(): void
    {
        static::assertSame(['shërlock', null], UriUtil::credentials(Uri::createFromString('https://sh%C3%ABrlock@example.com')));
    }

    /**
     * @covers ::credentials
     */
    public function testCredentialsUriWithCredentials(): void
    {
        static::assertSame(['sherlock', 'passw*rd'], UriUtil::credentials(Uri::createFromString('https://sherlock:passw%2Ard@example.com')));

        // with special character
        static::assertSame(['sher:lock', 'passw*rd'], UriUtil::credentials(Uri::createFromString('https://sher%3Alock:passw%2Ard@example.com')));
    }
}
