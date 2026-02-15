<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use PHPUnit\Framework\MockObject\Stub;
use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;

abstract class AbstractTestCase extends TestCase
{
    use AccessorPairAsserter;
    use TestTrait;

    protected Stub&LoggerInterface $logger;
    protected Envelope $envelope;

    protected function setUp(): void
    {
        $this->envelope = new Envelope(new stdClass(), []);
        $this->logger   = static::createStub(LoggerInterface::class);
    }

    /**
     * @template T
     * @param T[] $items
     *
     * @return Generator<T>
     */
    protected static function createGeneratorFrom(array $items): Generator
    {
        yield from $items;
    }
}
