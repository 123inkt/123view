<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Rule;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Rule\EditRuleViewModel;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Rule\EditRuleViewModel
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
