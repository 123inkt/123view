<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\RulesViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\RulesViewModel
 */
class RulesViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getRules
     */
    public function testAccessorPairs(): void
    {
        $collection = new ArrayCollection();
        $rules      = new RulesViewModel($collection);
        static::assertSame($collection, $rules->getRules());
    }
}
