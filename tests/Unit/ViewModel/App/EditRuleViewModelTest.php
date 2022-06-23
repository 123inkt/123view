<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App;

use DR\GitCommitNotification\Tests\AbstractTest;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\EditRuleViewModel
 */
class EditRuleViewModelTest extends AbstractTest
{
    /**
     * @covers ::setForm
     * @covers ::getForm
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(EditRuleViewModel::class);
    }
}
