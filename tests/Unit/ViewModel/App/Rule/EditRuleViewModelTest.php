<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Rule;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Rule\EditRuleViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Rule\EditRuleViewModel
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
