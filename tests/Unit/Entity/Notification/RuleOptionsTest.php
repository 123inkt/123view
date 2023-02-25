<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Notification;

use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Notification\RuleOptions
 */
class RuleOptionsTest extends AbstractTestCase
{
    /**
     * @covers ::getId
     * @covers ::getRule
     * @covers ::setRule
     * @covers ::getFrequency
     * @covers ::setFrequency
     * @covers ::getDiffAlgorithm
     * @covers ::setDiffAlgorithm
     * @covers ::isIgnoreSpaceAtEol
     * @covers ::setIgnoreSpaceAtEol
     * @covers ::isIgnoreSpaceChange
     * @covers ::setIgnoreSpaceChange
     * @covers ::isIgnoreAllSpace
     * @covers ::setIgnoreAllSpace
     * @covers ::isIgnoreBlankLines
     * @covers ::setIgnoreBlankLines
     * @covers ::isExcludeMergeCommits
     * @covers ::setExcludeMergeCommits
     * @covers ::getSubject
     * @covers ::setSubject
     * @covers ::getTheme
     * @covers ::setTheme
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new RuleOptions())->getId());
        static::assertAccessorPairs(RuleOptions::class);
    }
}
