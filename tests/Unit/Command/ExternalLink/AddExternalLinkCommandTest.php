<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Command\ExternalLink;

use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\Review\Command\ExternalLink\AddExternalLinkCommand;
use DR\Review\Entity\Config\ExternalLink;
use DR\Review\Repository\Config\ExternalLinkRepository;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;

#[CoversClass(AddExternalLinkCommand::class)]
class AddExternalLinkCommandTest extends AbstractTestCase
{
    private ExternalLinkRepository&MockObject $linkRepository;
    private AddExternalLinkCommand            $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
        $inputValidator       = new InputValidator(Validation::createValidator());
        $this->command        = new AddExternalLinkCommand($this->linkRepository, $inputValidator);
    }

    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $this->linkRepository
            ->expects($this->once())
            ->method('save')
            ->with((new ExternalLink())->setPattern('pattern')->setUrl('https://url/'));

        $tester = new CommandTester($this->command);
        $result = $tester->execute(['pattern' => 'pattern', 'url' => 'https://url/']);
        static::assertSame(Command::SUCCESS, $result);
    }
}
