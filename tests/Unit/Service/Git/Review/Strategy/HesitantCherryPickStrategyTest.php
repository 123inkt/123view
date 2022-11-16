<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Git\Review\Strategy;

use DR\GitCommitNotification\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Git\Review\Strategy\HesitantCherryPickStrategy
 * @covers ::__construct
 */
class HesitantCherryPickStrategyTest extends AbstractTestCase
{
    /**
     * @covers ::getDiffFiles
     */
    public function testGetDiffFiles(): void
    {
    }
}
