<?php
declare(strict_types=1);

namespace DR\Review\ExternalTool\Gitlab;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Git\IntegrationLink;
use DR\Review\Event\CommitEvent;
use DR\Review\Utility\Icon;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class GitlabIntegration implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly string $gitlabApiUrl, private readonly GitlabService $gitlabService)
    {
    }

    public function onCommitEvent(CommitEvent $event): void
    {
        if ($this->gitlabApiUrl === '') {
            return;
        }

        try {
            $this->tryAddLink($event->commit);
        } catch (Throwable $e) {
            $this->logger?->error('GitlabIntegration: failed to add integration link: ' . $e->getMessage(), ['exception' => $e]);
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

        $url = $this->gitlabService->getMergeRequestUrl((int)$projectId, $remoteRef);
        $url ??= $this->gitlabService->getBranchUrl((int)$projectId, $remoteRef);
        if ($url === null) {
            return;
        }

        $commit->integrationLinks[] = new IntegrationLink($url, $this->getIcon(), 'Gitlab');
    }

    private function getIcon(): string
    {
        return Icon::getBase64(dirname(__DIR__, 3) . '/assets/images/gitlab.png');
    }
}
