<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ExternalTool\Gitlab;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Event\CommitEvent;
use DR\Review\ExternalTool\Gitlab\GitlabIntegration;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitlabIntegration::class)]
class GitlabIntegrationTest extends AbstractTestCase
{
    private GitlabService&MockObject $gitlabService;
    private GitlabIntegration        $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitlabService = $this->createMock(GitlabService::class);
        $this->integration   = new GitlabIntegration('https://gitlab.example.com/', $this->gitlabService);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->gitlabService->expects($this->never())->method('getMergeRequestUrl');
        static::assertSame([CommitEvent::class => ['onCommitEvent']], GitlabIntegration::getSubscribedEvents());
    }

    public function testOnCommitEventShouldSkipOnMissingGitlabApiUrl(): void
    {
        $this->gitlabService = $this->createMock(GitlabService::class);
        $this->integration   = new GitlabIntegration('', $this->gitlabService);

        // setup mock
        $this->gitlabService->expects($this->never())->method('getMergeRequestUrl');

        $this->integration->onCommitEvent(new CommitEvent(static::createStub(Commit::class)));
    }

    public function testOnCommitEventShouldSkipOnMissingGitlabProjectId(): void
    {
        $repository = $this->createRepository('gitlab', 'https://git.repository.example.com/');

        $commit             = $this->createCommit();
        $commit->repository = $repository;

        // setup mock
        $this->gitlabService->expects($this->never())->method('getMergeRequestUrl');

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
        $this->gitlabService->expects($this->once())
            ->method('getMergeRequestUrl')
            ->with("123", $commit->getRemoteRef())
            ->willReturn(null);
        $this->gitlabService->expects($this->once())
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
        $this->gitlabService->expects($this->once())->method('getMergeRequestUrl')->willThrowException(new Exception());

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
        $this->gitlabService->expects($this->once())
            ->method('getMergeRequestUrl')
            ->with("123", $commit->getRemoteRef())
            ->willReturn('https://gitlab.example.com/merge-request/1');

        $this->integration->onCommitEvent(new CommitEvent($commit));
        static::assertNotEmpty($commit->integrationLinks);
    }
}
