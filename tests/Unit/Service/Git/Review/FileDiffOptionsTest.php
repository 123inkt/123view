<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review;

use DR\GitCommitNotification\Service\Git\Review\FileDiffOptions;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\FileDiffOptions
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
        static::assertSame('udl:5', (string)$options);
    }
}
