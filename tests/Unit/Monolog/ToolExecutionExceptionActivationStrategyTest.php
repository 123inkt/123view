<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Monolog;

use DateTimeImmutable;
use DR\Review\Exception\Ai\InvalidReviewUrlException;
use DR\Review\Monolog\ToolExecutionExceptionActivationStrategy;
use DR\Review\Tests\AbstractTestCase;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Bridge\Monolog\Handler\FingersCrossed\HttpCodeActivationStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[CoversClass(ToolExecutionExceptionActivationStrategy::class)]
class ToolExecutionExceptionActivationStrategyTest extends AbstractTestCase
{
    public function testToolExceptionDoesNotActivateHandler(): void
    {
        $inner = $this->createMock(ActivationStrategyInterface::class);
        $inner->expects($this->never())->method('isHandlerActivated');
        $strategy = new ToolExecutionExceptionActivationStrategy($inner);
        $record   = new LogRecord(new DateTimeImmutable(), 'mcp', Level::Error, 'Tool error', [
            'exception' => new InvalidReviewUrlException('https://gitlab.com/example/-/merge_requests/899'),
        ]);

        static::assertFalse($strategy->isHandlerActivated($record));
    }

    #[DataProvider('activationProvider')]
    public function testOtherRecordsDelegateToInnerStrategy(bool $activated): void
    {
        $record = new LogRecord(new DateTimeImmutable(), 'app', Level::Error, 'Unexpected error', [
            'exception' => new RuntimeException('Unexpected error'),
        ]);
        $inner = $this->createMock(ActivationStrategyInterface::class);
        $inner->expects($this->once())->method('isHandlerActivated')->with($record)->willReturn($activated);

        static::assertSame($activated, (new ToolExecutionExceptionActivationStrategy($inner))->isHandlerActivated($record));
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function activationProvider(): iterable
    {
        yield 'activated' => [true];
        yield 'not activated' => [false];
    }

    public function testMailerHttpExclusionsAndFileLogging(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/mcp'));
        $inner = new HttpCodeActivationStrategy(
            $requestStack,
            [['code' => 400, 'urls' => []], ['code' => 403, 'urls' => []], ['code' => 404, 'urls' => []], ['code' => 405, 'urls' => []]],
            new ErrorLevelActivationStrategy(Level::Error)
        );
        $mailHandler = new TestHandler();
        $fileHandler = new TestHandler();
        $logger      = new Logger('mcp', [
            new FingersCrossedHandler($mailHandler, new ToolExecutionExceptionActivationStrategy($inner)),
            $fileHandler,
        ]);

        $logger->error('Tool error', ['exception' => new InvalidReviewUrlException('invalid')]);
        foreach ([400, 403, 404, 405] as $code) {
            $logger->error('HTTP error', ['exception' => new HttpException($code)]);
        }
        $logger->info('Informational message');
        static::assertSame([], $mailHandler->getRecords());
        static::assertCount(6, $fileHandler->getRecords());

        $logger->error('Unexpected error');
        static::assertTrue($mailHandler->hasError('Unexpected error'));
    }
}
