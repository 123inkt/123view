<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Log;

use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory
 */
class FormatPatternFactoryTest extends AbstractTest
{
    /**
     * @covers ::createPattern
     */
    public function testCreatePattern(): void
    {
        $expected = FormatPatternFactory::COMMIT_DELIMITER .
            implode(FormatPatternFactory::PARTS_DELIMITER, FormatPatternFactory::PATTERN) .
            FormatPatternFactory::PARTS_DELIMITER;

        $factory = new FormatPatternFactory();
        static::assertSame($expected, $factory->createPattern());
    }
}
