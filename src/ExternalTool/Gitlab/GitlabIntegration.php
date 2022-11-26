<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\ExternalTool\Gitlab;

use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Entity\Git\IntegrationLink;
use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\Utility\Icon;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class GitlabIntegration implements EventSubscriberInterface
{
    private LoggerInterface $log;
    private string          $gitlabApiUrl;
    private GitlabApi       $api;

    public function __construct(LoggerInterface $log, string $gitlabApiUrl, GitlabApi $api)
    {
        $this->log          = $log;
        $this->gitlabApiUrl = $gitlabApiUrl;
        $this->api          = $api;
    }

    public function onCommitEvent(CommitEvent $event): void
    {
        if ($this->gitlabApiUrl === '') {
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
        $projectId = $commit->repository->getRepositoryProperty('gitlab-project-id');
        $remoteRef = $commit->getRemoteRef();

        if ($projectId === null || $remoteRef === null || is_numeric($projectId) === false) {
            return;
        }

        $url = $this->api->getMergeRequestUrl((int)$projectId, $remoteRef) ?? $this->api->getBranchUrl((int)$projectId, $remoteRef);
        if ($url === null) {
            return;
        }

        $commit->integrationLinks[] = new IntegrationLink($url, $this->getIcon(), 'Gitlab');
    }

    private function getIcon(): string
    {
        return Icon::getBase64(dirname(__DIR__, 3) . '/public/assets/images/gitlab.png');
    }
}
