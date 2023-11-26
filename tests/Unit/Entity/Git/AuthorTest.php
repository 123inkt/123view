<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Git;

use DR\Review\Entity\Git\Author;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Author::class)]
class AuthorTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $author = new Author('name', 'email');
        static::assertSame('name', $author->name);
        static::assertSame('email', $author->email);
    }
}
