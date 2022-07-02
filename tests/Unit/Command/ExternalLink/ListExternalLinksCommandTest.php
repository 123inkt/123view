<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\ExternalLink;

use DR\GitCommitNotification\Command\ExternalLink\ListExternalLinksCommand;
use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\ExternalLink\ListExternalLinksCommand
 * @covers ::__construct
 */
class ListExternalLinksCommandTest extends AbstractTestCase
{
    /** @var MockObject&ExternalLinkRepository */
    private ExternalLinkRepository   $linkRepository;
    private ListExternalLinksCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
        $this->command        = new ListExternalLinksCommand($this->linkRepository);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $link = (new ExternalLink())->setPattern('pattern')->setUrl('url');

        $this->linkRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$link]);

        $tester = new CommandTester($this->command);
        $result = $tester->execute([]);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteEmptyList(): void
    {
        $this->linkRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $result = $tester->execute([]);
        static::assertSame(Command::SUCCESS, $result);
    }
}
