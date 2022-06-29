<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Command\ExternalLink;

use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\GitCommitNotification\Command\ExternalLink\AddExternalLinkCommand;
use DR\GitCommitNotification\Entity\ExternalLink;
use DR\GitCommitNotification\Repository\ExternalLinkRepository;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Validator\Validation;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Command\ExternalLink\AddExternalLinkCommand
 * @covers ::__construct
 */
class AddExternalLinkCommandTestCase extends AbstractTestCase
{
    /** @var MockObject&ExternalLinkRepository */
    private ExternalLinkRepository $linkRepository;
    private AddExternalLinkCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(ExternalLinkRepository::class);
        $inputValidator       = new InputValidator(Validation::createValidator());
        $this->command        = new AddExternalLinkCommand($this->linkRepository, $inputValidator);
    }

    /**
     * @covers ::configure
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $input  = new ArrayInput(['pattern' => 'pattern', 'url' => 'https://url/']);
        $output = new NullOutput();

        $this->linkRepository
            ->expects(self::once())
            ->method('add')
            ->with((new ExternalLink())->setPattern('pattern')->setUrl('https://url/'));

        $result = $this->command->run($input, $output);
        static::assertSame(Command::SUCCESS, $result);
    }
}
