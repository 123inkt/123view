<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Review\FileDiffViewModel
 * @covers ::__construct
 */
class FileDiffViewModelTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    /**
     * @covers ::<public>
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(FileDiffViewModel::class);
    }

    /**
     * @covers ::getDiffModes
     */
    public function testGetDiffModes(): void
    {
        $file = new FileDiffViewModel(new Difffile(), ReviewDiffModeEnum::INLINE);
        static::assertSame(['side-by-side', 'unified', 'inline'], $file->getDiffModes());
    }
}
