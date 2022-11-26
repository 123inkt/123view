<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ExternalTool\Upsource;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\IntegrationLink;
use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\Utility\Icon;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class UpsourceIntegration implements EventSubscriberInterface
{
    private const REVIEW_URL = '%s%s/review/%s';

    private LoggerInterface $log;
    private string          $upsourceApiUrl;
    private UpsourceApi     $api;

    public function __construct(LoggerInterface $log, string $upsourceApiUrl, UpsourceApi $api)
    {
        $this->log            = $log;
        $this->upsourceApiUrl = $upsourceApiUrl;
        $this->api            = $api;
    }

    public function onCommitEvent(CommitEvent $event): void
    {
        if ($this->upsourceApiUrl === "") {
            return;
        }

        try {
            $this->tryAddLink($event->commit);
        } catch (Throwable $e) {
            $this->log->error('UpsourceIntegration: failed to add integration link: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [CommitEvent::class => ['onCommitEvent']];
    }

    /**
     * @throws Throwable
     */
    private function tryAddLink(Commit $commit): void
    {
        $projectId = $commit->repository->getRepositoryProperty('upsource-project-id');
        if ($projectId === null) {
            $this->log->debug('UpsourceIntegration: no projectId specified for repository: ' . $commit->repository->getName());

            return;
        }

        $reviewId = $this->api->getReviewId($projectId, $commit->getSubjectLine());
        if ($reviewId === null) {
            $this->log->debug(
                sprintf('UpsourceIntegration: no review found for `%s` in %s', $commit->getSubjectLine(), $commit->repository->getName())
            );

            return;
        }

        $url = sprintf(self::REVIEW_URL, $this->upsourceApiUrl, urlencode($projectId), urlencode($reviewId));

        $commit->integrationLinks[] = new IntegrationLink($url, $this->getIcon(), 'Upsource');
    }

    private function getIcon(): string
    {
        return Icon::getBase64(dirname(__DIR__, 3) . '/public/assets/images/upsource.png');
    }
}
