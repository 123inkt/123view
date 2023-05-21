<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileDiffViewModel::class)]
class FileDiffViewModelTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FileDiffViewModel::class);
    }

    public function testGetDiffModes(): void
    {
        $file = new FileDiffViewModel(new Difffile(), ReviewDiffModeEnum::INLINE);
        static::assertSame(['side-by-side', 'unified', 'inline'], $file->getDiffModes());
    }
}
