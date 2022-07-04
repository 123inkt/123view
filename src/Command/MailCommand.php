<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command;

use DateTimeImmutable;
use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Entity\Config\RuleConfiguration;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Service\RuleProcessor;
use DR\GitCommitNotification\Utility\Strings;
use InvalidArgumentException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand('mail', "With current configuration mail the latest commit changes")]
class MailCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private RuleRepository $ruleRepository,
        private ExternalLinkRepository $linkRepository,
        private RuleProcessor $ruleProcessor
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('--frequency', '-f', InputOption::VALUE_REQUIRED, 'The current frequency of the mail command.');
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @phpstan-var Frequency::* $frequency */
        $frequency = Strings::string($input->getOption('frequency'));
        if (Frequency::isValid($frequency) === false) {
            throw new InvalidArgumentException('Invalid or missing `frequency` argument: ' . $frequency);
        }

        // create date time object in seconds precisely 5 minutes earlier
        $currentTime = new DateTimeImmutable(date('Y-m-d H:i:00', strtotime("-5 minutes")));
        [$startTime, $endTime] = Frequency::getPeriod($currentTime, $frequency);

        // gather external links
        $externalLinks = $this->linkRepository->findAll();

        // gather active rules
        $rules = $this->ruleRepository->getActiveRulesForFrequency(true, $frequency);

        $exitCode = self::SUCCESS;
        foreach ($rules as $rule) {
            try {
                $this->ruleProcessor->processRule(new RuleConfiguration($startTime, $endTime, $externalLinks, $rule));
            } catch (Throwable $exception) {
                $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
                $exitCode = self::FAILURE;
            }
        }

        // one or more rules failed
        return $exitCode;
    }
}
