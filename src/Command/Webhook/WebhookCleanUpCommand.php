<?php
declare(strict_types=1);

namespace DR\Review\Command\Webhook;

use DR\Review\Repository\Webhook\WebhookActivityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('webhook:cleanup', "Clean up all the webhook activity from 2 weeks and older.")]
class WebhookCleanUpCommand extends Command
{
    public function __construct(private readonly WebhookActivityRepository $activityRepository)
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $removed = $this->activityRepository->cleanUp(strtotime("-2 weeks"));

        $output->writeln("Removed " . $removed . " webhook activities");

        return self::SUCCESS;
    }
}
