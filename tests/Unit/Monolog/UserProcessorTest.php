<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Monolog;

use DateTimeImmutable;
use DR\Review\Monolog\UserProcessor;
use DR\Review\Tests\AbstractTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(UserProcessor::class)]
class UserProcessorTest extends AbstractTestCase
{
    private TokenStorageInterface&MockObject $tokenStorage;
    private UserProcessor                    $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->processor    = new UserProcessor($this->tokenStorage);
    }

    public function testInvokeAddsUserIdentifierToContext(): void
    {
        $user = static::createStub(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('user@example.com');
        $token = static::createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn($token);

        $record = $this->createRecord(['message_id' => '123']);

        $processedRecord = ($this->processor)($record);

        static::assertSame(['message_id' => '123', 'user_id' => 'user@example.com'], $processedRecord->context);
    }

    public function testInvokeLeavesContextWithoutUser(): void
    {
        $this->tokenStorage->expects($this->once())->method('getToken')->willReturn(null);
        $record = $this->createRecord(['message_id' => '123']);

        $processedRecord = ($this->processor)($record);

        static::assertSame($record, $processedRecord);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function createRecord(array $context): LogRecord
    {
        return new LogRecord(new DateTimeImmutable(), 'app', Level::Info, 'Message', $context);
    }
}
