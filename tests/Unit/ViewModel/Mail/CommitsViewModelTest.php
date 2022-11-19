<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\Mail;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\Mail\CommitsViewModel
 * @covers ::__construct
 */
class CommitsViewModelTest extends AbstractTestCase
{
    use AccessorPairAsserter;

    /**
     * @covers ::getTheme
     * @covers ::getCommits
     */
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CommitsViewModel::class);
    }
}
