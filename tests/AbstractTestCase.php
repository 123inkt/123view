<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use function DR\Review\Tests\Unit\Service\Api\Gitlab\null;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractTestCase extends TestCase
{
    use AccessorPairAsserter;
    use TestTrait;

    /** @var MockObject&LoggerInterface */
    protected LoggerInterface $logger;

    protected Envelope $envelope;

    protected function setUp(): void
    {
        $this->envelope = new Envelope(new stdClass(), []);
        $this->logger   = $this->createMock(LoggerInterface::class);
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
