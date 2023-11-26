<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepositoryProperty::class)]
class RepositoryPropertyTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RepositoryProperty::class);
    }
}
