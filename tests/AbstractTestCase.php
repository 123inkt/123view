<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractTestCase extends TestCase
{
    use AccessorPairAsserter;
    use TestTrait;

    /** @var MockObject&LoggerInterface */
    protected LoggerInterface $log;

    protected function setUp(): void
    {
        $this->log = $this->createMock(LoggerInterface::class);
    }
}
