<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Review;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Review\FileDiffViewModel
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
