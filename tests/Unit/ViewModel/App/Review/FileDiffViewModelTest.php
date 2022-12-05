<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\FileDiffViewModel;

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
}
