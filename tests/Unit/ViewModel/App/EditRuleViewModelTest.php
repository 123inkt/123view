<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\EditRuleViewModel
 */
class EditRuleViewModelTest extends AbstractTestCase
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
