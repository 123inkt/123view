<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\ExternalLink;

use DR\GitCommitNotification\Command\ExternalLink\ListExternalLinksCommand;
use DR\GitCommitNotification\Entity\ExternalLink;
use DR\GitCommitNotification\Repository\ExternalLinkRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\ExternalLink\ListExternalLinksCommand
 * @covers ::__construct
 */
class ListExternalLinksCommandTestCase extends AbstractTestCase
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
        $input  = new ArrayInput([]);
        $output = new NullOutput();
        $link   = (new ExternalLink())->setPattern('pattern')->setUrl('url');

        $this->linkRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$link]);

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteEmptyList(): void
    {
        $input  = new ArrayInput([]);
        $output = new NullOutput();

        $this->linkRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([]);

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }
}
