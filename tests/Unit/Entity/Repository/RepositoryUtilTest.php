<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Entity\Repository\RepositoryUtil;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepositoryUtil::class)]
class RepositoryUtilTest extends AbstractTestCase
{
    public function testGetUriWithoutCredentials(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));

        static::assertSame('https://example.com', (string)RepositoryUtil::getUriWithCredentials($repository));
    }

    public function testGetUriWithCredentials(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://example.com'));
        $repository->setCredential((new RepositoryCredential())->setCredentials(new BasicAuthCredential('username', 'password')));

        static::assertSame('https://username:password@example.com', (string)RepositoryUtil::getUriWithCredentials($repository));
    }
}
