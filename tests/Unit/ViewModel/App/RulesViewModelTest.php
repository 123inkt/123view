<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App;

use DR\GitCommitNotification\Tests\AbstractTest;
use DR\GitCommitNotification\ViewModel\App\RulesViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\RulesViewModel
 */
class RulesViewModelTest extends AbstractTest
{
    /**
     * @covers ::setRules
     * @covers ::getRules
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(RulesViewModel::class);
    }
}
