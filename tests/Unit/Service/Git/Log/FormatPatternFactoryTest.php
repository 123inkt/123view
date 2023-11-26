<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Log;

use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FormatPatternFactory::class)]
class FormatPatternFactoryTest extends AbstractTestCase
{
    public function testCreatePattern(): void
    {
        $expected = FormatPatternFactory::COMMIT_DELIMITER .
            implode(FormatPatternFactory::PARTS_DELIMITER, FormatPatternFactory::PATTERN) .
            FormatPatternFactory::PARTS_DELIMITER;

        $factory = new FormatPatternFactory();
        static::assertSame($expected, $factory->createPattern());
    }
}
