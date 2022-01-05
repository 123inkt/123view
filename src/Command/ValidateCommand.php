<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Command;

use DR\GitCommitNotification\Entity\Config\Frequency;
use DR\GitCommitNotification\Exception\ConfigException;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class ValidateCommand extends Command
{
    private ConfigLoader $configLoader;

    public function __construct(ConfigLoader $configLoader)
    {
        parent::__construct();
        $this->configLoader = $configLoader;
    }

    protected function configure(): void
    {
        $this->setName("validate:config")
            ->setDescription("Verify current configuration is valid")
            ->addOption('--config', '-c', InputOption::VALUE_REQUIRED, 'The path to the config.xml');
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->configLoader->load(Frequency::ONCE_PER_DAY, $input);
        } catch (ConfigException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
