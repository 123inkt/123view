<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Git\Review\FileDiffOptions
 * @covers ::__construct
 */
class FileDiffOptionsTest extends AbstractTestCase
{
    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $options = new FileDiffOptions(5);
        static::assertSame('fdo-5', (string)$options);
    }
}
