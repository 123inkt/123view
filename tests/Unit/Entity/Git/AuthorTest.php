<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git;

use DR\Review\Entity\Git\Author;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Git\Author
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
