<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command;

use DR\GitCommitNotification\Utility\Strings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

#[AsCommand('test:mail', "Send a test mail to the given mail address")]
class TestMailCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->addArgument('address', InputArgument::REQUIRED, 'The test e-mail address');
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $address = Strings::string($input->getArgument('address'));

        $email = (new Email())
            ->addTo(new Address($address))
            ->subject('[Commit Notification] test mail')
            ->text('Git log test mail');

        $this->mailer->send($email);

        $output->writeln("Successfully send mail to: " . $address);

        return Command::SUCCESS;
    }
}
