<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ExternalTool\Gitlab;

use DR\GitCommitNotification\Entity\Config\RepositoryProperty;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Event\CommitEvent;
use DR\GitCommitNotification\ExternalTool\Gitlab\GitlabApi;
use DR\GitCommitNotification\ExternalTool\Gitlab\GitlabIntegration;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ExternalTool\Gitlab\GitlabIntegration
 * @covers ::__construct
 */
class GitlabIntegrationTest extends AbstractTestCase
{
    /** @var GitlabApi&MockObject */
    private GitlabApi         $api;
    private GitlabIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api         = $this->createMock(GitlabApi::class);
        $this->integration = new GitlabIntegration($this->log, 'https://gitlab.example.com/', $this->api);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([CommitEvent::class => ['onCommitEvent']], GitlabIntegration::getSubscribedEvents());
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     */
    public function testOnCommitEventShouldSkipOnMissingGitlabApiUrl(): void
    {
        $this->api         = $this->createMock(GitlabApi::class);
        $this->integration = new GitlabIntegration($this->log, '', $this->api);

        // setup mock
        $this->api->expects(static::never())->method('getMergeRequestUrl');

        $this->integration->onCommitEvent(new CommitEvent($this->createMock(Commit::class)));
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     * @covers ::getIcon
     */
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

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     * @covers ::getIcon
     */
    public function testOnCommitEventShouldSkipOnNoUrl(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

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

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     * @covers ::getIcon
     */
    public function testOnCommitEventShouldSkipOnHttpClientException(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

        $commit             = $this->createCommit();
        $commit->repository = $repository;
        $commit->refs       = 'refs/remotes/origin/remote-ref';

        // setup mock
        $this->api->expects(static::once())->method('getMergeRequestUrl')->willThrowException(new Exception());

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertEmpty($commit->integrationLinks);
    }

    /**
     * @covers ::onCommitEvent
     * @covers ::tryAddLink
     * @covers ::getIcon
     */
    public function testOnCommitEvent(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');
        $repository->addRepositoryProperty(new RepositoryProperty("gitlab-project-id", "123"));

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
