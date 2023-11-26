<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Rule\RulesViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RulesViewModel::class)]
class RulesViewModelTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $collection = new ArrayCollection();
        $rules      = new RulesViewModel($collection);
        static::assertSame($collection, $rules->getRules());
    }
}
