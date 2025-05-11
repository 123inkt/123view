<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep\Command;

use DR\Review\Service\Process\ProcessService;
use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilder;
use DR\Review\Service\Search\RipGrep\Command\RipGrepProcessExecutor;
use DR\Review\Service\Search\RipGrep\Iterator\ProcessOutputIterator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RipGrepProcessExecutor::class)]
class RipGrepProcessExecutorTest extends AbstractTestCase
{
    private ProcessService&MockObject $processService;
    private RipGrepProcessExecutor    $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processService = $this->createMock(ProcessService::class);
        $this->executor       = new RipGrepProcessExecutor($this->processService);
    }

    public function testExecute(): void
    {
        $handle = popen(PHP_BINARY . ' -v', 'r');
        static::assertNotFalse($handle);

        $command = '/usr/bin/rg ' . escapeshellarg("foo") . ' ' . escapeshellarg("bar");
        $commandBuilder = $this->createMock(RipGrepCommandBuilder::class);
        $commandBuilder->method('build')->willReturn($command);

        $this->processService->expects(self::once())->method('popen')->with($command, 'r')->willReturn($handle);

        $iterator = $this->executor->execute($commandBuilder, __DIR__);
        $expected = new ProcessOutputIterator($handle);
        static::assertEquals($expected, $iterator);
    }
}
