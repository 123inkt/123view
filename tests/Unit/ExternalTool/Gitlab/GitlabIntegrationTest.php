<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ExternalTool\Gitlab;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Event\CommitEvent;
use DR\Review\ExternalTool\Gitlab\GitlabIntegration;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitlabIntegration::class)]
class GitlabIntegrationTest extends AbstractTestCase
{
    private GitlabApi&MockObject $api;
    private GitlabIntegration    $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api         = $this->createMock(GitlabApi::class);
        $this->integration = new GitlabIntegration($this->logger, 'https://gitlab.example.com/', $this->api);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([CommitEvent::class => ['onCommitEvent']], GitlabIntegration::getSubscribedEvents());
    }

    public function testOnCommitEventShouldSkipOnMissingGitlabApiUrl(): void
    {
        $this->api         = $this->createMock(GitlabApi::class);
        $this->integration = new GitlabIntegration($this->logger, '', $this->api);

        // setup mock
        $this->api->expects(static::never())->method('getMergeRequestUrl');

        $this->integration->onCommitEvent(new CommitEvent($this->createMock(Commit::class)));
    }

    public function testOnCommitEventShouldSkipOnMissingGitlabProjectId(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->api->expects(static::never())->method('getMergeRequestUrl');

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    public function testOnCommitEventShouldSkipOnNoUrl(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->setRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;
        $commit->refs       = 'refs/remotes/origin/remote-ref';

        // setup mocks
        $this->api->expects(static::once())
            ->method('getMergeRequestUrl')
            ->with("123", $commit->getRemoteRef())
            ->willReturn(null);
        $this->api->expects(static::once())
            ->method('getBranchUrl')
            ->with("123", $commit->getRemoteRef())
            ->willReturn(null);

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    public function testOnCommitEventShouldSkipOnHttpClientException(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->setRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;
        $commit->refs       = 'refs/remotes/origin/remote-ref';

        // setup mock
        $this->api->expects(static::once())->method('getMergeRequestUrl')->willThrowException(new Exception());

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    public function testOnCommitEvent(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->setRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;
        $commit->refs       = 'refs/remotes/origin/remote-ref';

        // setup mock
        $this->api->expects(static::once())
            ->method('getMergeRequestUrl')
            ->with("123", $commit->getRemoteRef())
            ->willReturn('https://gitlab.example.com/merge-request/1');

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertNotEmpty($commit->integrationLinks);
    }
}
