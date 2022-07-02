<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\ExternalLink;

use DR\GitCommitNotification\Command\ExternalLink\RemoveExternalLinkCommand;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\ExternalLink\RemoveExternalLinkCommand
 * @covers ::__construct
 */
class RemoveExternalLinkCommandTest extends AbstractTestCase
{
    /** @var MockObject&ExternalLinkRepository */
    private ExternalLinkRepository    $linkRepository;
    private RemoveExternalLinkCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
        $this->command        = new RemoveExternalLinkCommand($this->linkRepository);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $link = (new ExternalLink())->setPattern('pattern')->setUrl('url');

        $this->linkRepository->expects(self::once())->method('find')->with('id')->willReturn($link);
        $this->linkRepository->expects(self::once())->method('remove')->with($link, true);

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['id' => 'id']);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteFailure(): void
    {
        $this->linkRepository->expects(self::once())->method('find')->with('id')->willReturn(null);
        $this->linkRepository->expects(self::never())->method('remove');

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['id' => 'id']);
        static::assertSame(Command::FAILURE, $result);
    }
}
