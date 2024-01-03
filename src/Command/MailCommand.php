<?php
declare(strict_types=1);

namespace DR\Review\Command;

use DateTimeImmutable;
use DR\Review\Doctrine\Type\NotificationSendType;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\RuleConfiguration;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Mail\CommitMailService;
use DR\Review\Service\Notification\RuleNotificationService;
use DR\Review\Service\Revision\RevisionFetchService;
use DR\Review\Service\RuleProcessor;
use DR\Utils\Assert;
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
        private readonly RuleRepository $ruleRepository,
        private readonly RuleProcessor $ruleProcessor,
        private readonly RevisionFetchService $revisionFetchService,
        private readonly RuleNotificationService $notificationService,
        private readonly CommitMailService $mailService
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
        $frequency = Assert::string($input->getOption('frequency'));
        if (Frequency::isValid($frequency) === false) {
            throw new InvalidArgumentException('Invalid or missing `frequency` argument: ' . $frequency);
        }

        // create date time object in seconds precisely 5 minutes earlier, and with the interval given by the frequency
        $period = Frequency::getPeriod(new DateTimeImmutable(date('Y-m-d H:i:00', strtotime("-5 minutes"))), $frequency);

        // gather active rules
        $rules = $this->ruleRepository->getActiveRulesForFrequency(true, $frequency);

        // ensure all repositories are up-to-date
        $this->revisionFetchService->fetchRevisionsForRules($rules);

        $exitCode = self::SUCCESS;
        foreach ($rules as $rule) {
            if ($rule->getUser()->hasRole(Roles::ROLE_USER) === false) {
                continue;
            }

            try {
                $ruleConfig = new RuleConfiguration($period, $rule);
                $commits    = $this->ruleProcessor->processRule($ruleConfig);
                if (count($commits) === 0) {
                    $this->logger?->info('Found 0 new commits, ending...');
                    continue;
                }

                // register notification
                $notification = null;
                if (Assert::notNull($rule->getRuleOptions())->hasSendType(NotificationSendType::BROWSER)) {
                    $notification = $this->notificationService->addRuleNotification($rule, $period);
                }

                // send mail
                if (Assert::notNull($rule->getRuleOptions())->hasSendType(NotificationSendType::MAIL)) {
                    $this->mailService->sendCommitsMail($ruleConfig, $commits, $notification);
                }
            } catch (Throwable $exception) {
                $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
                $exitCode = self::FAILURE;
            }
        }

        // one or more rules failed
        return $exitCode;
    }
}
