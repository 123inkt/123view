<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\ExternalLink;

use DR\Review\Command\ExternalLink\RemoveExternalLinkCommand;
use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Repository\Config\ExternalLinkRepository;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(RemoveExternalLinkCommand::class)]
class RemoveExternalLinkCommandTest extends AbstractTestCase
{
    private ExternalLinkRepository&MockObject $linkRepository;
    private RemoveExternalLinkCommand         $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
        $this->command        = new RemoveExternalLinkCommand($this->linkRepository);
    }

    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $link = (new ExternalLink())->setPattern('pattern')->setUrl('url');

        $this->linkRepository->expects($this->once())->method('find')->with('id')->willReturn($link);
        $this->linkRepository->expects($this->once())->method('remove')->with($link, true);

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['id' => 'id']);
        static::assertSame(Command::SUCCESS, $result);
    }

    /**
     * @throws Exception
     */
    public function testExecuteFailure(): void
    {
        $this->linkRepository->expects($this->once())->method('find')->with('id')->willReturn(null);
        $this->linkRepository->expects(self::never())->method('remove');

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['id' => 'id']);
        static::assertSame(Command::FAILURE, $result);
    }
}
