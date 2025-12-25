<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDiffOptions::class)]
class FileDiffOptionsTest extends AbstractTestCase
{
    public function testToString(): void
    {
        $options = new FileDiffOptions(5, DiffComparePolicy::TRIM, CodeReviewType::BRANCH, 123);
        static::assertSame('fdo-5-trim-branch-123-no-raw', (string)$options);
    }
}
