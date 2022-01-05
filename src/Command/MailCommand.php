<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command;

use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use DR\GitCommitNotification\Service\RuleProcessor;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class MailCommand extends Command
{
    private LoggerInterface $logger;
    private ConfigLoader    $configLoader;
    private RuleProcessor   $ruleProcessor;

    public function __construct(LoggerInterface $logger, ConfigLoader $configLoader, RuleProcessor $ruleProcessor)
    {
        parent::__construct();
        $this->logger        = $logger;
        $this->configLoader  = $configLoader;
        $this->ruleProcessor = $ruleProcessor;
    }

    protected function configure(): void
    {
        $this->setName("mail")
            ->setDescription("With current configuration mail the latest commit changes")
            ->addOption('--frequency', '-f', InputOption::VALUE_REQUIRED, 'The current frequency of the mail command.')
            ->addOption('--config', '-c', InputOption::VALUE_REQUIRED, 'The path to the config.xml');
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $frequency = $input->getOption('frequency');
        if (Frequency::isValid($frequency) === false) {
            throw new InvalidArgumentException('Invalid or missing `frequency` argument: ' . $frequency);
        }

        try {
            $config = $this->configLoader->load($frequency, $input);
        } catch (ConfigException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;
        foreach ($config->getRules() as $rule) {
            if ($rule->active === false) {
                $this->logger->info(sprintf('Skipping rule `%s`, because it is not active', $rule->name));
                continue;
            }

            // verify the current frequency matches the rule frequency
            if ($rule->frequency !== $frequency) {
                $this->logger->info(sprintf('Skipping rule `%s` based on frequency: %s vs %s', $rule->name, $rule->frequency, $frequency));
                continue;
            }

            try {
                $this->ruleProcessor->processRule($rule);
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage(), ['exception' => $exception]);
                $exitCode = self::FAILURE;
            }
        }

        // one or more rules failed
        return $exitCode;
    }
}
