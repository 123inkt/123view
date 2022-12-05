<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Repository;

use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Repository\RepositoryProperty
 */
class RepositoryPropertyTest extends AbstractTestCase
{
    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RepositoryProperty::class);
    }
}
