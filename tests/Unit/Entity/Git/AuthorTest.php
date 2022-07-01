<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Git;

use DR\GitCommitNotification\Entity\Git\Author;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Git\Author
 */
class AuthorTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $author = new Author('name', 'email');
        static::assertSame('name', $author->name);
        static::assertSame('email', $author->email);
    }
}
