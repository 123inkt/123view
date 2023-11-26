<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Rule;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Rule\EditRuleViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EditRuleViewModel::class)]
class EditRuleViewModelTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(EditRuleViewModel::class);
    }
}
