<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Notification;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Notification\RuleNotificationViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RuleNotificationViewModel::class)]
class RuleNotificationViewModelTest extends AbstractTestCase
{
    public function testGetNotificationCount(): void
    {
        $viewModel = new RuleNotificationViewModel(null, [123 => 456], [], [], false);

        $rule = (new Rule())->setId(123);
        static::assertSame(456, $viewModel->getNotificationCount($rule));

        $rule = (new Rule())->setId(-1);
        static::assertSame(0, $viewModel->getNotificationCount($rule));
    }
}
